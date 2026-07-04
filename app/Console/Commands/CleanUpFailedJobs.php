<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\JobListing;

#[Signature('jobs:cleanup-failed')]
#[Description('Clean up job listings that failed during AI extraction')]
class CleanUpFailedJobs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of failed AI extracted jobs...');

        $deletedCount = JobListing::where('job_title', 'like', '%(AI Raw)%')
            ->orWhere('company_name', 'like', '%Under Verification%')
            ->orWhere('requirements', 'like', '%Ekstraksi AI mengalami kendala%')
            ->delete();

        $this->info("Successfully deleted {$deletedCount} failed job(s).");
    }
}
