<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\JobListing;
use App\Models\Platform;

class JobBoard extends Component
{
    public $platforms;
    public $selectedPlatform = null;
    public $searchQuery = '';
    public $locationFilter = 'Indonesia';
    public $dateFilter = 'All';
    public $selectedJob = null;
    public $perPage = 10;
    
    // Dynamic Twitter-style notification properties
    public $initialLatestJobId = null;
    public $newJobsCount = 0;
    
    public function mount()
    {
        $this->platforms = Platform::where('is_active', true)->get();
        $this->resetNotification();
    }

    private function resetNotification()
    {
        $this->initialLatestJobId = JobListing::max('id') ?? 0;
        $this->newJobsCount = 0;
    }

    public function selectPlatform($platformId)
    {
        $this->selectedPlatform = $this->selectedPlatform === $platformId ? null : $platformId;
        $this->selectedJob = null;
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function updatingSearchQuery()
    {
        // Reset limit and notifications on new search
        $this->perPage = 10;
        $this->resetNotification();
    }
    
    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function updatedLocationFilter()
    {
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function updatedDateFilter()
    {
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function checkForNewJobs()
    {
        if (!$this->initialLatestJobId) {
            $this->initialLatestJobId = JobListing::max('id') ?? 0;
            $this->newJobsCount = 0;
            return;
        }

        $this->newJobsCount = JobListing::where('id', '>', $this->initialLatestJobId)
            ->when($this->selectedPlatform, function ($query) {
                $query->where('platform_id', $this->selectedPlatform);
            })
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('job_title', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('company_name', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->locationFilter !== 'All', function ($query) {
                $query->where('location', $this->locationFilter);
            })
            ->when($this->dateFilter !== 'All', function ($query) {
                if ($this->dateFilter === 'Past 24 Hours') {
                    $query->where('created_at', '>=', now()->subDay());
                } elseif ($this->dateFilter === 'Past Week') {
                    $query->where('created_at', '>=', now()->subWeek());
                } elseif ($this->dateFilter === 'Past Month') {
                    $query->where('created_at', '>=', now()->subMonth());
                }
            })
            ->count();
    }

    public function showNewJobs()
    {
        $this->resetNotification();
        $this->perPage = 10; // reset listing limit to show newest immediately
    }

    public function selectJob($jobId)
    {
        $this->selectedJob = JobListing::with('platform')->find($jobId);
    }

    public function closeJobDetails()
    {
        $this->selectedJob = null;
    }

    public function getJobsProperty()
    {
        return JobListing::with('platform')
            ->when($this->selectedPlatform, function ($query) {
                $query->where('platform_id', $this->selectedPlatform);
            })
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('job_title', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('company_name', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->locationFilter !== 'All', function ($query) {
                $query->where('location', $this->locationFilter);
            })
            ->when($this->dateFilter !== 'All', function ($query) {
                if ($this->dateFilter === 'Past 24 Hours') {
                    $query->where('created_at', '>=', now()->subDay());
                } elseif ($this->dateFilter === 'Past Week') {
                    $query->where('created_at', '>=', now()->subWeek());
                } elseif ($this->dateFilter === 'Past Month') {
                    $query->where('created_at', '>=', now()->subMonth());
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit($this->perPage)
            ->get();
    }

    public function render()
    {
        $jobs = $this->jobs;
            
        // Count total for infinite scroll check
        $totalJobs = JobListing::when($this->selectedPlatform, function ($query) {
                $query->where('platform_id', $this->selectedPlatform);
            })
            ->when($this->searchQuery, function ($query) {
                $query->where(function ($q) {
                    $q->where('job_title', 'ilike', '%' . $this->searchQuery . '%')
                      ->orWhere('company_name', 'ilike', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->locationFilter !== 'All', function ($query) {
                $query->where('location', $this->locationFilter);
            })
            ->when($this->dateFilter !== 'All', function ($query) {
                if ($this->dateFilter === 'Past 24 Hours') {
                    $query->where('created_at', '>=', now()->subDay());
                } elseif ($this->dateFilter === 'Past Week') {
                    $query->where('created_at', '>=', now()->subWeek());
                } elseif ($this->dateFilter === 'Past Month') {
                    $query->where('created_at', '>=', now()->subMonth());
                }
            })->count();

        return view('livewire.job-board', [
            'jobs' => $jobs,
            'totalJobs' => $totalJobs
        ])->layout('layouts.app');
    }
}
