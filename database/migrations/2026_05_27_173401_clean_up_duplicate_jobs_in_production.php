<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\JobListing;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Log::info('Migration: Memulai pembersihan data duplikat di Production...');

        $allJobs = JobListing::orderBy('created_at', 'desc')->get();
        
        $seenUrls = [];
        $totalDeleted = 0;

        foreach ($allJobs as $job) {
            $baseUrl = explode('?', $job->source_url)[0];

            if (in_array($baseUrl, $seenUrls)) {
                $job->delete();
                $totalDeleted++;
            } else {
                $seenUrls[] = $baseUrl;
                
                if ($job->source_url !== $baseUrl) {
                    $job->source_url = $baseUrl;
                    $job->save();
                }
            }
        }

        Log::info("Migration: Berhasil membersihkan {$totalDeleted} data duplikat di Production.");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration for data cleanup
    }
};
