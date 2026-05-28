<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ApplicationTracker extends Component
{
    public function updateStatus($jobId, $newStatus)
    {
        $user = auth()->user();
        
        $validStatuses = ['saved', 'applied', 'interviewing', 'accepted', 'rejected'];
        
        if (in_array($newStatus, $validStatuses)) {
            $user->savedJobs()->updateExistingPivot($jobId, ['status' => $newStatus]);
        }
    }

    public function deleteJob($jobId)
    {
        $user = auth()->user();
        $user->savedJobs()->detach($jobId);
    }

    public function getApplicationsProperty()
    {
        $user = auth()->user();
        if (!$user) return collect();

        $jobs = $user->savedJobs()->with('platform')->get();

        return collect([
            'saved' => $jobs->where('pivot.status', 'saved'),
            'applied' => $jobs->where('pivot.status', 'applied'),
            'interviewing' => $jobs->where('pivot.status', 'interviewing'),
            'accepted' => $jobs->where('pivot.status', 'accepted'),
            'rejected' => $jobs->where('pivot.status', 'rejected'),
        ]);
    }

    public function render()
    {
        return view('livewire.application-tracker', [
            'applications' => $this->applications
        ]);
    }
}
