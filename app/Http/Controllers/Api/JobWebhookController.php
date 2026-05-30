<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

        // --- 3.5 STRATEGI BARU: Ekstrak ID Unik dari URL Sumber ---
        $cleanSourceUrl = $validated['source_url'];
        $uniqueJobId = null;

        // Ambil ID angka murni dari URL LinkedIn atau JobStreet untuk akurasi database
        if (preg_match('/(?:job\/|view\/)(\d+)/i', $cleanSourceUrl, $matches)) {
            $uniqueJobId = $matches[1];
        }

        // 4. PREVENSI DUPLIKAT LEVEL 1: Berdasarkan ID Lowongan atau URL Dasarnya
        $existingJob = JobListing::where(function($query) use ($cleanSourceUrl, $uniqueJobId) {
            $query->where('source_url', $cleanSourceUrl)
                  ->orWhere('source_url', explode('?', $cleanSourceUrl)[0]);
            
            if ($uniqueJobId) {
                $query->orWhere('source_url', 'LIKE', '%' . $uniqueJobId . '%');
            }
        })->first();

        // --- PREVENSI DUPLIKAT LEVEL 2: String Normalization (Kombinasi Konten) ---
        if (!$existingJob) {
            $incomingCompany = trim($validated['company_name'] ?? 'Confidential');
            $incomingTitle = trim($validated['job_title']);
            $incomingLocation = trim($validated['location'] ?? 'Indonesia');

            // Bikin fungsi helper lokal untuk membersihkan string (buang spasi hantu, tanda baca, lowercase)
            $sanitize = function($string) {
                return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $string));
            };

            $sanitizedTitle = $sanitize($incomingTitle);
            $sanitizedCompany = $sanitize($incomingCompany);
            $sanitizedLocation = $sanitize($incomingLocation);

            // Cari lowongan aktif di database (misal maksimal 14 hari terakhir) yang kontennya mirip
            $existingJob = JobListing::where('created_at', '>=', now()->subDays(14))
                ->get() // Ambil chunk data untuk divalidasi di memory agar ringan
                ->first(function($job) use ($sanitizedTitle, $sanitizedCompany, $sanitizedLocation, $sanitize) {
                    // Jika nama perusahaan bukan 'Confidential', bandingkan kesamaan kombinasi data
                    if ($sanitize($job->company_name) !== 'confidential') {
                        return $sanitize($job->company_name) === $sanitizedCompany 
                            && $sanitize($job->job_title) === $sanitizedTitle
                            && $sanitize($job->location ?? 'Indonesia') === $sanitizedLocation;
                    }
                    return false;
                });
        }

        if ($existingJob) {
            Log::info('Hermes API Webhook: Duplicate detected via Content/URL Hash, skipping.', [
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
                $parsedPostedAt = Carbon::parse($validated['posted_at']);
            } catch (\Exception $e) {
                Log::warning('Hermes API Webhook: Invalid posted_at format, fallback to now.', [
                    'posted_at' => $validated['posted_at']
                ]);
                $parsedPostedAt = now();
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
                'source_url' => explode('?', $cleanSourceUrl)[0], // Simpan versi bersih tanpa tracking
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
        $token = $request->header('X-Hermes-Token');
        $expectedToken = config('services.hermes.webhook_token');

        if (!$token || $token !== $expectedToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $stats = [
            'cpu_usage' => $request->input('cpu_usage', '0%'),
            'ram_usage' => $request->input('ram_usage', '0MB'),
            'scraped_today' => $request->input('scraped_today', 0),
            'status' => $request->input('status', 'running'),
            'last_seen' => now()->timestamp,
        ];

        \Illuminate\Support\Facades\Cache::put('hermes_status', $stats, 86400);

        return response()->json([
            'message' => 'Heartbeat received successfully',
            'timestamp' => now()->timestamp
        ], 200);
    }
}
