<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\JobListing;
use App\Models\Platform;

#[Layout('layouts.app')]
class JobBoard extends Component
{
    public $platforms;
    public $selectedPlatform = null;
    public $searchQuery = '';
    public $locationFilter = 'All';
    public $dateFilter = 'All';
    public $provinceFilter = 'All';
    public $ageFilter = 'All';
    public $salaryFilter = 'All';
    public $matchSkills = false;
    public $selectedJob = null;
    public $perPage = 10;
    public $showSavedOnly = false;
    
    // Dynamic Twitter-style notification properties
    public $initialLatestJobId = null;
    public $newJobsCount = 0;
    
    public function mount()
    {
        $this->platforms = Platform::where('is_active', true)
            ->whereIn('name', ['LinkedIn', 'Karirhub Kemnaker', 'KitaLulus', 'Loker.id']) // Tampilkan LinkedIn, Karirhub Kemnaker, KitaLulus & Loker.id
            ->get();
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

    public function updatedProvinceFilter()
    {
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function updatedAgeFilter()
    {
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function updatedSalaryFilter()
    {
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function updatedMatchSkills()
    {
        $this->perPage = 10;
        $this->resetNotification();
    }

    public function checkForNewJobs()
    {
        if ($this->showSavedOnly) {
            $this->newJobsCount = 0;
            return;
        }

        if (!$this->initialLatestJobId) {
            $this->initialLatestJobId = JobListing::max('id') ?? 0;
            $this->newJobsCount = 0;
            return;
        }

        // Count ALL new jobs regardless of currently active filters
        $this->newJobsCount = JobListing::where('id', '>', $this->initialLatestJobId)->count();
    }

    public function showNewJobs()
    {
        $this->searchQuery = '';
        $this->locationFilter = 'All';
        $this->dateFilter = 'All';
        $this->provinceFilter = 'All';
        $this->ageFilter = 'All';
        $this->salaryFilter = 'All';
        $this->matchSkills = false;
        $this->selectedPlatform = null;
        $this->showSavedOnly = false;
        
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

    public function toggleSaveJob($jobId)
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $user = auth()->user();
        if ($user->savedJobs()->where('job_listing_id', $jobId)->exists()) {
            $user->savedJobs()->detach($jobId);
        } else {
            $user->savedJobs()->attach($jobId);
        }

        // Refresh selected job data if it is open so UI updates immediately
        if ($this->selectedJob && $this->selectedJob->id == $jobId) {
            $this->selectedJob = JobListing::with('platform')->find($jobId);
        }
    }

    public function toggleSavedFilter()
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $this->showSavedOnly = !$this->showSavedOnly;
        
        // Reset platform filter if showing saved to prevent empty states
        if ($this->showSavedOnly) {
            $this->selectedPlatform = null;
        }

        $this->perPage = 10;
        $this->selectedJob = null;
        $this->resetNotification();
    }

    public function getJobsProperty()
    {
        return JobListing::with(['platform', 'savedByUsers'])
            ->when($this->selectedPlatform, function ($query) {
                $query->where('platform_id', $this->selectedPlatform);
            })
            ->when($this->showSavedOnly, function ($query) {
                $query->whereHas('savedByUsers', function ($q) {
                    $q->where('user_id', auth()->id());
                });
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
            ->when($this->provinceFilter !== 'All', function ($query) {
                $query->where('province', $this->provinceFilter);
            })
            ->when($this->ageFilter !== 'All', function ($query) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('min_age')
                            ->orWhere('min_age', '<=', $this->ageFilter);
                    })->where(function ($sub) {
                        $sub->whereNull('max_age')
                            ->orWhere('max_age', '>=', $this->ageFilter);
                    });
                });
            })
            ->when($this->salaryFilter !== 'All', function ($query) {
                if ($this->salaryFilter === '< 3 Juta') {
                    $query->where(function ($q) {
                        $q->where('min_salary', '<=', 3000000)
                          ->orWhere('max_salary', '<=', 3000000);
                    });
                } elseif ($this->salaryFilter === '3 - 5 Juta') {
                    $query->where(function ($q) {
                        $q->whereBetween('min_salary', [3000000, 5000000])
                          ->orWhereBetween('max_salary', [3000000, 5000000]);
                    });
                } elseif ($this->salaryFilter === '5 - 10 Juta') {
                    $query->where(function ($q) {
                        $q->whereBetween('min_salary', [5000000, 10000000])
                          ->orWhereBetween('max_salary', [5000000, 10000000]);
                    });
                } elseif ($this->salaryFilter === '> 10 Juta') {
                    $query->where(function ($q) {
                        $q->where('min_salary', '>=', 10000000)
                          ->orWhere('max_salary', '>=', 10000000);
                    });
                }
            })
            ->when($this->matchSkills && auth()->check() && auth()->user()->skills, function ($query) {
                $skills = array_filter(array_map('trim', explode(',', auth()->user()->skills)));
                if (!empty($skills)) {
                    $query->where(function ($q) use ($skills) {
                        foreach ($skills as $skill) {
                            $q->orWhere('job_title', 'ilike', '%' . $skill . '%')
                              ->orWhereRaw("requirements::text ilike ?", ['%' . $skill . '%']);
                        }
                    });
                }
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
            ->when($this->showSavedOnly, function ($query) {
                $query->whereHas('savedByUsers', function ($q) {
                    $q->where('user_id', auth()->id());
                });
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
            ->when($this->provinceFilter !== 'All', function ($query) {
                $query->where('province', $this->provinceFilter);
            })
            ->when($this->ageFilter !== 'All', function ($query) {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('min_age')
                            ->orWhere('min_age', '<=', $this->ageFilter);
                    })->where(function ($sub) {
                        $sub->whereNull('max_age')
                            ->orWhere('max_age', '>=', $this->ageFilter);
                    });
                });
            })
            ->when($this->salaryFilter !== 'All', function ($query) {
                if ($this->salaryFilter === '< 3 Juta') {
                    $query->where(function ($q) {
                        $q->where('min_salary', '<=', 3000000)
                          ->orWhere('max_salary', '<=', 3000000);
                    });
                } elseif ($this->salaryFilter === '3 - 5 Juta') {
                    $query->where(function ($q) {
                        $q->whereBetween('min_salary', [3000000, 5000000])
                          ->orWhereBetween('max_salary', [3000000, 5000000]);
                    });
                } elseif ($this->salaryFilter === '5 - 10 Juta') {
                    $query->where(function ($q) {
                        $q->whereBetween('min_salary', [5000000, 10000000])
                          ->orWhereBetween('max_salary', [5000000, 10000000]);
                    });
                } elseif ($this->salaryFilter === '> 10 Juta') {
                    $query->where(function ($q) {
                        $q->where('min_salary', '>=', 10000000)
                          ->orWhere('max_salary', '>=', 10000000);
                    });
                }
            })
            ->when($this->matchSkills && auth()->check() && auth()->user()->skills, function ($query) {
                $skills = array_filter(array_map('trim', explode(',', auth()->user()->skills)));
                if (!empty($skills)) {
                    $query->where(function ($q) use ($skills) {
                        foreach ($skills as $skill) {
                            $q->orWhere('job_title', 'ilike', '%' . $skill . '%')
                              ->orWhereRaw("requirements::text ilike ?", ['%' . $skill . '%']);
                        }
                    });
                }
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

        // Read status from Cache
        // $hermesStatus = \Illuminate\Support\Facades\Cache::get('hermes_status');
        // $hermesOnline = false;

        // if ($hermesStatus && isset($hermesStatus['last_seen'])) {
        //     // Online if last heartbeat is within 6 minutes (360 seconds)
        //     if (now()->timestamp - $hermesStatus['last_seen'] < 360) {
        //         $hermesOnline = true;
        //     }
        // }

        $provinces = JobListing::whereNotNull('province')
            ->where('province', '<>', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        return view('livewire.job-board', [
            'jobs' => $jobs,
            'totalJobs' => $totalJobs,
            // 'hermesOnline' => $hermesOnline,
            // 'hermesStatus' => $hermesStatus,
            'provinces' => $provinces
        ])->layout('layouts.app');
    }
}
