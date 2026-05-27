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
        ]);

        // 3. Resolve active platform by name
        $platform = Platform::where('name', $validated['platform_name'])->first();
        if (!$platform) {
            Log::error('Hermes API Webhook: Platform not found or inactive.', [
                'platform_name' => $validated['platform_name']
            ]);
            return response()->json(['message' => 'Platform not found'], 422);
        }

        // 4. Duplicate prevention (source URL basis)
        $existingJob = JobListing::where('source_url', $validated['source_url'])->first();
        if ($existingJob) {
            Log::info('Hermes API Webhook: Job already exists, skipping.', [
                'source_url' => $validated['source_url'],
                'job_id' => $existingJob->id
            ]);
            return response()->json([
                'message' => 'Job already exists',
                'job_id' => $existingJob->id
            ], 200);
        }

        // 5. Store structured Job Listing
        try {
            $job = JobListing::create([
                'platform_id' => $platform->id,
                'job_title' => $validated['job_title'],
                'company_name' => $validated['company_name'] ?? 'Confidential',
                'company_logo' => $validated['company_logo'],
                'requirements' => $validated['requirements'] ? json_encode($validated['requirements']) : null,
                'source_url' => $validated['source_url'],
                'location' => $validated['location'] ?? 'Indonesia',
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
