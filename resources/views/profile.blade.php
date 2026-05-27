<x-app-layout>
    <x-slot name="header">
        <div class="max-w-4xl mx-auto">
            <h2 class="font-extrabold text-2xl text-gray-900 tracking-tight leading-tight">
                {{ __('Account Settings') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Manage your personal information, security, and account preferences.</p>
        </div>
    </x-slot>

    <div class="py-10 pb-24">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="p-6 sm:p-10 bg-white border border-gray-100 shadow-sm sm:rounded-3xl transition-all duration-300 hover:shadow-md">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="p-6 sm:p-10 bg-white border border-gray-100 shadow-sm sm:rounded-3xl transition-all duration-300 hover:shadow-md">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="p-6 sm:p-10 bg-red-50/30 border border-red-100 shadow-sm sm:rounded-3xl transition-all duration-300 hover:shadow-md">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation for Profile -->
    <div class="md:hidden fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 flex justify-around items-center px-4 shadow-lg">
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 text-gray-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-medium text-gray-500 group-hover:text-red-600 transition-colors">Home</span>
        </a>
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 text-gray-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <span class="text-[10px] font-medium text-gray-500 group-hover:text-red-600 transition-colors">Search</span>
        </a>
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 text-gray-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
            <span class="text-[10px] font-medium text-gray-500 group-hover:text-red-600 transition-colors">Saved</span>
        </a>
        <a href="{{ route('profile') }}" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] font-medium text-red-600 transition-colors">Profile</span>
        </a>
    </div>
</x-app-layout>
