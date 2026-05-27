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
        $this->info('Memulai pencarian data duplikat berdasarkan Base URL...');

        // Ambil semua data dari yang paling baru
        $allJobs = JobListing::orderBy('created_at', 'desc')->get();
        
        $seenUrls = [];
        $totalDeleted = 0;

        foreach ($allJobs as $job) {
            // Bersihkan URL dari parameter tracking (?refId=..., dll)
            $baseUrl = explode('?', $job->source_url)[0];

            if (in_array($baseUrl, $seenUrls)) {
                // Jika URL dasar ini sudah pernah kita temukan sebelumnya (yang mana lebih baru),
                // maka job ini adalah duplikat versi lamanya. Hapus!
                $job->delete();
                $totalDeleted++;
            } else {
                // Tandai URL ini sudah dilihat (ini adalah versi terbaru karena orderBy desc)
                $seenUrls[] = $baseUrl;
                
                // Jika URL di database masih ada query string-nya, kita bersihkan sekalian
                if ($job->source_url !== $baseUrl) {
                    $job->source_url = $baseUrl;
                    $job->save();
                }
            }
        }

        $this->info("Selesai! Berhasil menghapus {$totalDeleted} data duplikat.");
    }
}
