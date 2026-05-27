<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav class="bg-white shadow-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Left: Logo & Brand -->
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                    MP
                </div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight hidden sm:block">Loker Merah Putih</h1>
            </a>
            <!-- Hermes Status (Static on Profile) -->
            <div class="flex items-center gap-1.5 ml-1 sm:ml-2 bg-gray-50 border border-gray-100 rounded-full px-2 py-0.5 transition-all duration-300" title="Hermes Agent">
                <span class="relative flex h-2 w-2">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-[9px] font-extrabold tracking-wider uppercase text-green-600">
                    Hermes
                </span>
            </div>
        </div>

        <!-- Right: Auth Links -->
        <div class="flex items-center gap-4">
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.platforms') }}" wire:navigate class="text-sm font-bold text-gray-600 hover:text-red-600 transition-colors hidden sm:block">Platforms</a>
            @endif
            <a href="{{ route('profile') }}" wire:navigate class="text-sm font-bold {{ request()->routeIs('profile') ? 'text-red-600' : 'text-gray-600 hover:text-red-600' }} transition-colors hidden sm:block">Profile</a>
            
            <button wire:click="logout" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors ml-2 pl-4 border-l border-gray-200">
                Log Out
            </button>
        </div>
    </div>
</nav>
