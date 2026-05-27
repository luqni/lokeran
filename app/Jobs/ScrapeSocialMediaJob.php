<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Platform;
use App\Models\RawJob;
use App\Models\JobListing;

class ScrapeSocialMediaJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $platformNames = [
            'LinkedIn',
        ];

        // Round-robin selection using cache
        $currentIndex = Cache::get('last_scraped_platform_index', -1);
        $nextIndex = ($currentIndex + 1) % count($platformNames);
        Cache::put('last_scraped_platform_index', $nextIndex, 86400);

        $selectedPlatformName = $platformNames[$nextIndex];

        // Find active platform model
        $platform = Platform::where('name', $selectedPlatformName)->where('is_active', true)->first();
        if (!$platform) {
            // Fallback to any active platform if not found
            $platform = Platform::where('is_active', true)->first();
            if (!$platform) {
                Log::error("No active platforms found for scraping.");
                return;
            }
            $selectedPlatformName = $platform->name;
        }

        $locations = ['Indonesia', 'Worldwide'];
        $selectedLocation = $locations[array_rand($locations)];

        $platformConfigs = [
            'LinkedIn' => [
                'search_url' => "https://www.linkedin.com/jobs/search?location={$selectedLocation}&f_TPR=r604800",
                'regex' => '/(https:\/\/[a-z]{2}\.linkedin\.com\/jobs\/view\/[^)\s"\'<>]+)/',
            ],
            'JobStreet' => [
                'search_url' => "https://id.jobstreet.com/id/jobs?daterange=7",
                'regex' => '/(https:\/\/(?:id|www)\.jobstreet\.(?:com|co\.id)\/id\/job\/[^)\s"\'<>?]+)/',
            ],
            'Indeed' => [
                'search_url' => "https://id.indeed.com/jobs?q=dibutuhkan+segera&l=Indonesia",
                'regex' => '/(https:\/\/id\.indeed\.com\/(?:rc\/clk|viewjob)\?[^)\s"\'<>]+)/',
            ],
            'Karir.com' => [
                'search_url' => "https://www.karir.com/search",
                'regex' => '/(https:\/\/www\.karir\.com\/opportunities\/[0-9]+)/',
            ],
            'Loker.id' => [
                'search_url' => "https://www.loker.id/cari-lowongan-kerja",
                'regex' => '/(https:\/\/www\.loker\.id\/(?:lowongan\/[^)\s"\'<>]+|[^)\s"\'<>]+\/[0-9]+-[^)\s"\'<>]+\.html))/',
            ],
            'Karirhub Kemnaker' => [
                'search_url' => "https://karirhub.kemnaker.go.id/vacancies",
                'regex' => '/(https:\/\/karirhub\.kemnaker\.go\.id\/(?:vacancies|lowongan)\/[a-f0-9\-]+)/',
            ],
        ];

        $config = $platformConfigs[$selectedPlatformName] ?? $platformConfigs['LinkedIn'];
        $searchUrl = "https://r.jina.ai/" . $config['search_url'];

        Log::info("Starting round-robin scrape run.", [
            'platform' => $selectedPlatformName,
            'search_url' => $config['search_url'],
            'location' => $selectedLocation
        ]);

        try {
            $response = Http::timeout(60)->get($searchUrl);
            
            if ($response->successful()) {
                $markdown = $response->body();
                
                // Extract job detail URLs using regex
                preg_match_all($config['regex'], $markdown, $matches);
                $jobUrls = array_unique($matches[1] ?? []);
                
                // Limit to 5 newest jobs to avoid rate limiting
                $jobUrls = array_slice($jobUrls, 0, 5);
                
                Log::info("Scraped search page successfully. Found job URLs.", [
                    'platform' => $selectedPlatformName,
                    'total_found' => count($matches[1] ?? []),
                    'limit_taken' => count($jobUrls)
                ]);

                foreach ($jobUrls as $index => $jobUrl) {
                    // Check if already exists in JobListing or RawJob to prevent duplicates
                    if (JobListing::where('source_url', $jobUrl)->exists() || 
                        RawJob::where('source_url', $jobUrl)->exists()) {
                        continue;
                    }

                    try {
                        // Fetch details using Jina Reader
                        $detailResponse = Http::timeout(60)->get('https://r.jina.ai/' . $jobUrl);
                        
                        if ($detailResponse->successful()) {
                            $rawText = $detailResponse->body();
                            
                            $rawJob = RawJob::create([
                                'platform_id' => $platform->id,
                                'raw_content' => substr($rawText, 0, 5000), // Limit text length for AI API
                                'status' => 'pending',
                                'location' => $selectedLocation,
                                'source_url' => $jobUrl
                            ]);

                            // Dispatch the AI processing job
                            ProcessRawJobWithAI::dispatch($rawJob);
                            
                            Log::info("Enqueued raw job details for AI processing.", [
                                'raw_job_id' => $rawJob->id,
                                'source_url' => $jobUrl
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Error scraping details for URL {$jobUrl}: " . $e->getMessage());
                        continue;
                    }
                }
            } else {
                Log::error("Jina Search API failed for platform {$selectedPlatformName}: " . $response->status(), [
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Scraper Search API Error for platform {$selectedPlatformName}: " . $e->getMessage());
        }
    }
}
