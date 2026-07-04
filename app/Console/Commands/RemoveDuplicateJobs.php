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
        $this->info('Memulai pencarian data duplikat berdasarkan URL dan Judul+Perusahaan...');

        // Ambil semua data dari yang paling baru
        $allJobs = JobListing::orderBy('created_at', 'desc')->get();
        
        $seenUrls = [];
        $seenTitles = [];
        $totalDeleted = 0;

        foreach ($allJobs as $job) {
            // Bersihkan URL dari parameter tracking (?refId=..., dll)
            $baseUrl = explode('?', (string)$job->source_url)[0];
            
            // Normalisasi URL Karirhub (vacancies dan lowongan adalah hal yang sama)
            $normalizedUrl = str_replace('/vacancies/', '/lowongan/', $baseUrl);

            // Buat key dari judul dan perusahaan
            $titleKey = strtolower(trim((string)$job->job_title)) . '|' . strtolower(trim((string)$job->company_name));

            if (in_array($normalizedUrl, $seenUrls) || in_array($titleKey, $seenTitles)) {
                // Jika URL atau (Judul+Perusahaan) ini sudah pernah kita temukan sebelumnya (yang mana lebih baru),
                // maka job ini adalah duplikat versi lamanya. Hapus!
                $job->delete();
                $totalDeleted++;
            } else {
                // Tandai sudah dilihat (ini adalah versi terbaru karena orderBy desc)
                if ($normalizedUrl) {
                    $seenUrls[] = $normalizedUrl;
                }
                if ($titleKey !== '|') {
                    $seenTitles[] = $titleKey;
                }
                
                // Jika URL di database masih ada query string-nya, kita bersihkan sekalian
                if ($job->source_url && $job->source_url !== $baseUrl) {
                    $job->source_url = $baseUrl;
                    $job->save();
                }
            }
        }

        $this->info("Selesai! Berhasil menghapus {$totalDeleted} data duplikat.");
    }
}
