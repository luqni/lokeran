<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\JobListing;
use Illuminate\Support\Facades\DB;

#[Signature('jobs:remove-duplicates')]
#[Description('Menghapus data lowongan kerja yang duplikat (berdasarkan judul dan nama perusahaan) dan menyisakan yang terbaru')]
class RemoveDuplicateJobs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pencarian data duplikat...');

        // Cari job yang duplikat berdasarkan judul dan nama perusahaan
        $duplicates = JobListing::select('job_title', 'company_name', DB::raw('COUNT(*) as count'))
            ->groupBy('job_title', 'company_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('Tidak ada data duplikat yang ditemukan.');
            return;
        }

        $totalDeleted = 0;

        foreach ($duplicates as $duplicate) {
            // Ambil semua job yang sama persis, urutkan dari yang terbaru
            $jobs = JobListing::where('job_title', $duplicate->job_title)
                ->where('company_name', $duplicate->company_name)
                ->orderBy('created_at', 'desc')
                ->get();

            // Pertahankan yang paling baru (index 0), hapus sisanya
            $jobsToDelete = $jobs->slice(1);
            
            foreach ($jobsToDelete as $job) {
                $job->delete();
                $totalDeleted++;
            }
        }

        $this->info("Selesai! Berhasil menghapus {$totalDeleted} data duplikat.");
    }
}
