<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\RawJob;
use App\Models\JobListing;
use App\Services\GeminiApiService;
use Exception;

class ProcessRawJobWithAI implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $rawJob;
    public $tries = 5;
    public $backoff = 30;

    public function __construct(RawJob $rawJob)
    {
        $this->rawJob = $rawJob;
    }

    public function handle(GeminiApiService $geminiService): void
    {
        if ($this->rawJob->status === 'processed') {
            return;
        }

        // Allow 10 requests per minute
        $executed = RateLimiter::attempt(
            'gemini-api-limit',
            10,
            function() {
                // Return true so we know it executed
                return true;
            },
            60 // 60 seconds
        );

        if (! $executed) {
            \Illuminate\Support\Facades\Log::warning("Gemini API Rate Limit hit. Releasing job for RawJob ID: {$this->rawJob->id}");
            // Release the job back to the queue with a delay
            $this->release(30);
            return;
        }

        try {
            $extractedData = $geminiService->extractJobData($this->rawJob->raw_content);

            if ($extractedData && isset($extractedData['job_title'])) {
                JobListing::create([
                    'platform_id' => $this->rawJob->platform_id,
                    'job_title' => $extractedData['job_title'],
                    'company_name' => $extractedData['company_name'] ?? null,
                    'company_logo' => $extractedData['company_logo'] ?? null,
                    'requirements' => isset($extractedData['requirements']) ? json_encode($extractedData['requirements']) : null,
                    'source_url' => $this->rawJob->source_url, // Ambil dari URL asli saat di-scrape
                    'location' => $this->rawJob->location,
                ]);

                $this->rawJob->update(['status' => 'processed']);
            } else {
                // If it fails to extract structured data, we might want to log it or mark as failed
                $this->rawJob->update(['status' => 'failed']);
            }
        } catch (Exception $e) {
            $this->release(60);
        }
    }
}
