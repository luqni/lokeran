<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobWebhookController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate security API token
        $token = $request->header('X-Hermes-Token');
        $expectedToken = config('services.hermes.webhook_token');

        if (!$token || $token !== $expectedToken) {
            Log::warning('Hermes API Webhook unauthorized access attempt.', [
                'ip' => $request->ip(),
                'received_token' => $token ? substr($token, 0, 5) . '...' : 'none'
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Validate input fields
        $validated = $request->validate([
            'platform_name' => 'required|string',
            'job_title' => 'required|string',
            'company_name' => 'nullable|string',
            'company_logo' => 'nullable|url',
            'requirements' => 'nullable|array',
            'source_url' => 'required|url',
            'location' => 'nullable|string',
            'posted_at' => 'nullable|string',
        ]);

        // 3. Resolve active platform by name
        $platform = Platform::where('name', $validated['platform_name'])->first();
        if (!$platform) {
            Log::error('Hermes API Webhook: Platform not found or inactive.', [
                'platform_name' => $validated['platform_name']
            ]);
            return response()->json(['message' => 'Platform not found'], 422);
        }

        // 3.5 Normalize source URL (Remove tracking parameters like ?refId=...)
        $cleanSourceUrl = explode('?', $validated['source_url'])[0];

        // 4. Duplicate prevention (source URL basis)
        $existingJob = JobListing::where('source_url', $cleanSourceUrl)
            ->orWhere('source_url', $validated['source_url'])
            ->first();

        // Also check if very similar job exists across ANY platform to prevent duplicates
        // "Mirip banget": Same company, and exact same title (ignoring case/platform)
        if (!$existingJob) {
            $existingJob = JobListing::where('company_name', $validated['company_name'] ?? 'Confidential')
                ->where('job_title', $validated['job_title'])
                ->first();
        }

        // Additional fuzzy check: if the title is very similar (contains the word) for the same company
        if (!$existingJob && isset($validated['company_name'])) {
            $existingJob = JobListing::where('company_name', $validated['company_name'])
                ->where(function($query) use ($validated) {
                    $query->where('job_title', 'LIKE', '%' . $validated['job_title'] . '%')
                          ->orWhereRaw("? LIKE CONCAT('%', job_title, '%')", [$validated['job_title']]);
                })->first();
        }

        if ($existingJob) {
            Log::info('Hermes API Webhook: Job already exists (duplicate detected), skipping.', [
                'job_title' => $validated['job_title'],
                'company' => $validated['company_name'] ?? 'Confidential',
                'job_id' => $existingJob->id
            ]);
            return response()->json([
                'message' => 'Job already exists',
                'job_id' => $existingJob->id
            ], 200);
        }

        // Parse posted_at safely
        $parsedPostedAt = null;
        if (!empty($validated['posted_at'])) {
            try {
                // If it's something like "2 hari lalu", Carbon might throw an exception or return a weird date.
                // We just let Carbon try to parse it. If it fails, we fall back to null.
                $parsedPostedAt = \Carbon\Carbon::parse($validated['posted_at']);
            } catch (\Exception $e) {
                Log::warning('Hermes API Webhook: Invalid posted_at format, ignoring.', [
                    'posted_at' => $validated['posted_at']
                ]);
                $parsedPostedAt = null;
            }
        }

        // 5. Store structured Job Listing
        try {
            $job = JobListing::create([
                'platform_id' => $platform->id,
                'job_title' => $validated['job_title'],
                'company_name' => $validated['company_name'] ?? 'Confidential',
                'company_logo' => $validated['company_logo'],
                'requirements' => $validated['requirements'] ? json_encode($validated['requirements']) : null,
                'source_url' => $cleanSourceUrl,
                'location' => $validated['location'] ?? 'Indonesia',
                'posted_at' => $parsedPostedAt,
            ]);

            Log::info('Hermes API Webhook: Successfully stored new job listing.', [
                'job_id' => $job->id,
                'title' => $job->job_title,
                'company' => $job->company_name
            ]);

            return response()->json([
                'message' => 'Job successfully saved',
                'job_id' => $job->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Hermes API Webhook: Failed to save job listing.', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            return response()->json(['message' => 'Internal server error while saving job'], 500);
        }
    }

    public function heartbeat(Request $request)
    {
        // 1. Validate security API token
        $token = $request->header('X-Hermes-Token');
        $expectedToken = config('services.hermes.webhook_token');

        if (!$token || $token !== $expectedToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Read statistics from payload
        $stats = [
            'cpu_usage' => $request->input('cpu_usage', '0%'),
            'ram_usage' => $request->input('ram_usage', '0MB'),
            'scraped_today' => $request->input('scraped_today', 0),
            'status' => $request->input('status', 'running'),
            'last_seen' => now()->timestamp,
        ];

        // 3. Save to cache
        \Illuminate\Support\Facades\Cache::put('hermes_status', $stats, 86400); // 1 day cache

        return response()->json([
            'message' => 'Heartbeat received successfully',
            'timestamp' => now()->timestamp
        ], 200);
    }
}
