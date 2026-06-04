<?php

namespace Tests\Feature;

use App\Models\JobListing;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobBoardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_jobs_by_province_and_age(): void
    {
        // 1. Create a platform
        $platform = Platform::create([
            'name' => 'LinkedIn',
            'is_active' => true,
        ]);

        // 2. Create Job 1: DKI Jakarta, age 20-30
        $jobJakarta = JobListing::create([
            'platform_id' => $platform->id,
            'job_title' => 'Jakarta Developer',
            'company_name' => 'Tech Corp',
            'source_url' => 'https://linkedin.com/jobs/1',
            'location' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'min_age' => 20,
            'max_age' => 30,
        ]);

        // 3. Create Job 2: Jawa Barat, age 25-35
        $jobJabar = JobListing::create([
            'platform_id' => $platform->id,
            'job_title' => 'Bandung Engineer',
            'company_name' => 'Dev Corp',
            'source_url' => 'https://linkedin.com/jobs/2',
            'location' => 'Bandung',
            'province' => 'Jawa Barat',
            'min_age' => 25,
            'max_age' => 35,
        ]);

        // 4. Test filtering with Livewire
        
        // Initial state (No filters): Should see both jobs
        Livewire::test(\App\Livewire\JobBoard::class)
            ->assertViewHas('jobs', function ($jobs) {
                return $jobs->count() === 2;
            });

        // Filter by Province: DKI Jakarta
        Livewire::test(\App\Livewire\JobBoard::class)
            ->set('provinceFilter', 'DKI Jakarta')
            ->assertViewHas('jobs', function ($jobs) {
                return $jobs->count() === 1 && $jobs->first()->job_title === 'Jakarta Developer';
            });

        // Filter by Province: Jawa Barat
        Livewire::test(\App\Livewire\JobBoard::class)
            ->set('provinceFilter', 'Jawa Barat')
            ->assertViewHas('jobs', function ($jobs) {
                return $jobs->count() === 1 && $jobs->first()->job_title === 'Bandung Engineer';
            });

        // Filter by Age: 22 (jakarta developer requires 20-30, bandung requires 25-35)
        Livewire::test(\App\Livewire\JobBoard::class)
            ->set('ageFilter', 22)
            ->assertViewHas('jobs', function ($jobs) {
                return $jobs->count() === 1 && $jobs->first()->job_title === 'Jakarta Developer';
            });

        // Filter by Age: 32
        Livewire::test(\App\Livewire\JobBoard::class)
            ->set('ageFilter', 32)
            ->assertViewHas('jobs', function ($jobs) {
                return $jobs->count() === 1 && $jobs->first()->job_title === 'Bandung Engineer';
            });

        // Filter by Age: 40 (Neither job allows age 40)
        Livewire::test(\App\Livewire\JobBoard::class)
            ->set('ageFilter', 40)
            ->assertViewHas('jobs', function ($jobs) {
                return $jobs->count() === 0;
            });
    }
}
