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

<div>
<nav class="bg-white shadow-sm sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Left: Logo & Brand -->
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                    MP
                </div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight whitespace-nowrap">Loker Merah Putih</h1>
            </a>
            <!-- Hermes Status (Static on Profile) -->
            <div class="flex items-center gap-1.5 ml-1 sm:ml-2 bg-gray-50 border border-gray-100 rounded-full px-2 py-0.5 transition-all duration-300" title="Hermes Agent">
                <span class="relative flex h-2 w-2">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-[9px] font-extrabold tracking-wider uppercase text-green-600">
                    <span class="hidden sm:inline">di tenagai oleh </span>Hermes AI
                </span>
            </div>
        </div>

        <!-- Right: Auth Links -->
        <div class="flex items-center gap-4">
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.platforms') }}" wire:navigate class="text-sm font-bold text-gray-600 hover:text-red-600 transition-colors hidden sm:block">Platforms</a>
            @endif
            <a href="{{ route('applications') }}" wire:navigate class="text-sm font-bold {{ request()->routeIs('applications') ? 'text-red-600' : 'text-gray-600 hover:text-red-600' }} transition-colors hidden sm:block">Lamaran Saya</a>
            <a href="{{ route('profile') }}" wire:navigate class="text-sm font-bold {{ request()->routeIs('profile') ? 'text-red-600' : 'text-gray-600 hover:text-red-600' }} transition-colors hidden sm:block">Profile</a>
            
            <button wire:click="logout" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors ml-2 pl-4 border-l border-gray-200">
                Log Out
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Bottom Navigation (Global for Profile, Applications, etc.) -->
<div class="md:hidden fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 flex justify-around items-center px-4 shadow-lg">
    <a href="{{ route('home') }}" class="flex flex-col items-center justify-center w-full group">
        <svg class="w-6 h-6 mb-1 {{ request()->routeIs('home') ? 'text-red-600' : 'text-gray-500' }} group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <span class="text-[10px] font-medium {{ request()->routeIs('home') ? 'text-red-600' : 'text-gray-500' }} group-hover:text-red-600 transition-colors">Home</span>
    </a>
    <a href="{{ route('applications') }}" class="flex flex-col items-center justify-center w-full group">
        <svg class="w-6 h-6 mb-1 {{ request()->routeIs('applications') ? 'text-red-600' : 'text-gray-500' }} group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
        <span class="text-[10px] font-medium {{ request()->routeIs('applications') ? 'text-red-600' : 'text-gray-500' }} group-hover:text-red-600 transition-colors">Lamaran</span>
    </a>
    <a href="{{ route('profile') }}" class="flex flex-col items-center justify-center w-full group">
        <svg class="w-6 h-6 mb-1 {{ request()->routeIs('profile') ? 'text-red-600' : 'text-gray-500' }} group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <span class="text-[10px] font-medium {{ request()->routeIs('profile') ? 'text-red-600' : 'text-gray-500' }} group-hover:text-red-600 transition-colors">Profile</span>
    </a>
</div>
</div>


