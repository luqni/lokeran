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
            <!-- Premium Profile Hero Card -->
            <div class="p-6 sm:p-8 bg-gradient-to-br from-gray-900 to-indigo-950 text-white shadow-xl sm:rounded-3xl flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden">
                <!-- Background decoration -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-20 top-0 w-24 h-24 bg-red-600/10 rounded-full blur-lg pointer-events-none"></div>
                
                <div class="flex flex-col sm:flex-row items-center gap-5 z-10 text-center sm:text-left">
                    <!-- User Avatar -->
                    <div class="w-20 h-20 bg-white/10 rounded-2xl flex items-center justify-center text-3xl font-extrabold text-white border border-white/20 shadow-inner backdrop-blur-sm uppercase">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold tracking-tight">{{ auth()->user()->name }}</h3>
                        <p class="text-xs text-gray-300 mt-0.5">{{ auth()->user()->email }}</p>
                        <div class="mt-2.5 flex flex-wrap gap-2 justify-center sm:justify-start">
                            @if(auth()->user()->hasVerifiedEmail())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-500/20 text-green-300 border border-green-500/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Belum Verifikasi
                                </span>
                            @endif

                            @if(auth()->user()->isAdmin())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500/20 text-red-300 border border-red-500/30">
                                    Administrator
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="text-right z-10 flex flex-col items-center sm:items-end gap-1">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Anggota Sejak</span>
                    <span class="text-sm font-semibold text-gray-200">{{ auth()->user()->created_at->format('d M Y') }}</span>
                </div>
            </div>

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


</x-app-layout>
