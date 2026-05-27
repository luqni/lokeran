<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Platform;

class PlatformManager extends Component
{
    public $platforms;
    public $name = '';
    public $icon_path = '';
    
    public function mount()
    {
        $this->loadPlatforms();
    }

    public function loadPlatforms()
    {
        $this->platforms = Platform::all();
    }

    public function addPlatform()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'icon_path' => 'nullable|string',
        ]);

        Platform::create([
            'name' => $this->name,
            'icon_path' => $this->icon_path,
            'is_active' => true,
        ]);

        $this->name = '';
        $this->icon_path = '';
        $this->loadPlatforms();
    }

    public function toggleStatus($id)
    {
        $platform = Platform::find($id);
        if ($platform) {
            $platform->is_active = !$platform->is_active;
            $platform->save();
            $this->loadPlatforms();
        }
    }

    public function deletePlatform($id)
    {
        $platform = Platform::find($id);
        if ($platform) {
            $platform->delete();
            $this->loadPlatforms();
        }
    }

    public function render()
    {
        return view('livewire.admin.platform-manager');
    }
}
