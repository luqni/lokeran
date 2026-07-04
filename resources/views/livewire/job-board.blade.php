<div x-data="{ 
    searchFocused: false, 
    searchVal: @entangle('searchQuery'),
    selectedJob: @entangle('selectedJob')
}" x-init="
    $watch('searchVal', val => { if(val) searchFocused = true; });
" x-effect="
    if (selectedJob) {
        document.documentElement.classList.add('overflow-hidden', 'lg:overflow-auto');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    } else {
        document.documentElement.classList.remove('overflow-hidden', 'lg:overflow-auto');
        document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
    }
" class="min-h-screen bg-gray-50 flex flex-col font-sans" wire:poll.15s="checkForNewJobs">
    
    <!-- Global Fullscreen Loader -->
    <div wire:loading.flex wire:target="locationFilter, dateFilter, searchQuery, selectPlatform, toggleSavedFilter, selectJob, showNewJobs" class="fixed inset-0 z-[100] bg-white/70 backdrop-blur-md flex-col items-center justify-center transition-all duration-300">
        <div class="flex flex-col items-center justify-center gap-6 p-8 rounded-3xl bg-white/50 shadow-2xl shadow-red-500/5 ring-1 ring-black/5">
            <div class="relative w-20 h-20 flex items-center justify-center">
                <!-- Spinning outer ring -->
                <div class="absolute inset-0 rounded-full border-4 border-red-50 border-t-red-600 border-r-red-600 animate-spin" style="animation-duration: 1s;"></div>
                
                <!-- Inner Logo -->
                <div class="w-14 h-14 bg-gradient-to-br from-red-600 to-red-700 rounded-full flex items-center justify-center shadow-lg shadow-red-600/30">
                    <span class="text-white font-black text-xl tracking-tight">MP</span>
                </div>
            </div>
            
            <div class="flex flex-col items-center gap-2">
                <h2 class="text-lg font-black text-gray-900 tracking-tight">Loker Merah Putih</h2>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-bounce" style="animation-delay: -0.3s"></span>
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-bounce" style="animation-delay: -0.15s"></span>
                    <span class="w-2 h-2 rounded-full bg-red-400 animate-bounce"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Row 1: Logo & Auth/Saved -->
            <div class="flex items-center justify-between h-14 md:h-16 border-b border-gray-100">
                <!-- Left: Logo & Hermes Indicator -->
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                        MP
                    </div>
                    <h1 class="text-lg md:text-xl font-bold text-gray-900 tracking-tight whitespace-nowrap">Loker Merah Putih</h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Bell -->
                    @auth
                    <button onclick="subscribeToPushNotifications()" class="relative p-2 transition-colors text-gray-400 hover:text-amber-500" title="Aktifkan Notifikasi Loker Baru">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                    @endauth

                    <!-- Saved Filter -->
                    <button wire:click="toggleSavedFilter" class="relative p-2 transition-colors {{ $showSavedOnly ? 'text-red-600' : 'text-gray-400 hover:text-gray-500' }}" title="Saved Jobs">
                        <svg class="w-6 h-6 {{ $showSavedOnly ? 'fill-red-600 text-red-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </button>

                    <!-- Auth Links -->
                    
                </div>
            </div>

            <!-- Row 2: Search & Filters (Always below Logo) -->
            <div class="py-2 flex items-center h-14">
                
                <div x-data="{ 
                    canScrollLeft: false, 
                    canScrollRight: false,
                    checkScroll() {
                        const el = $refs.filterScroll;
                        if (!el) return;
                        this.canScrollLeft = el.scrollLeft > 5;
                        this.canScrollRight = el.scrollWidth - el.clientWidth - el.scrollLeft > 5;
                    }
                }" x-init="setTimeout(() => checkScroll(), 200); $watch('searchVal', () => $nextTick(() => checkScroll()))" @resize.window.debounce.100ms="checkScroll()" class="relative flex-1 lg:flex-none flex items-center h-full min-w-0">
                    
                    <!-- Left Gradient Overlay (Mobile/Tablet) -->
                    <div x-show="canScrollLeft" class="lg:hidden absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>

                    <!-- Left Arrow Button (Desktop Only) -->
                    <button type="button" x-show="canScrollLeft" @click="$refs.filterScroll.scrollBy({ left: -120, behavior: 'smooth' })" style="left: -0.5rem;" class="hidden lg:flex absolute z-20 bg-white/95 hover:bg-white border border-gray-200 shadow-md rounded-full p-1 text-gray-500 hover:text-red-600 transition-all items-center justify-center w-7 h-7">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <!-- Scrollable Container -->
                    <div x-ref="filterScroll" @scroll="checkScroll()" class="flex items-center gap-2 w-full lg:w-auto h-full overflow-x-auto scrollbar-hide py-1 -mx-4 px-4 lg:mx-0 lg:px-0">
                        <!-- Desktop Search (Always visible, fixed width) -->
                        <div class="hidden lg:flex relative items-center w-64 h-full flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 z-10 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search jobs, companies..." class="w-full h-full pl-10 pr-4 rounded-full border border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                        </div>

                        <!-- Mobile Search (Expandable) -->
                        <div class="flex lg:hidden items-center h-full transition-all duration-300 flex-shrink-0" :class="searchFocused ? 'w-full flex-1' : 'w-10 flex-none'">
                            <!-- Closed State Button -->
                            <button type="button" x-show="!searchFocused" @click="searchFocused = true; $nextTick(() => $refs.searchInput.focus())" class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full border border-gray-300 bg-white text-gray-500 shadow-sm hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </button>
                            <!-- Open State Input -->
                            <div x-show="searchFocused" style="display: none;" class="relative w-full h-full flex items-center">
                                <svg class="w-5 h-5 text-red-500 absolute left-3 z-10 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <input x-ref="searchInput" @blur="if(!searchVal) searchFocused = false" type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search jobs..." class="w-full h-full pl-10 pr-10 rounded-full border border-red-500 ring-2 ring-red-500/20 bg-white text-sm shadow-sm focus:outline-none">
                                <button type="button" x-show="searchVal" @click="searchVal = ''; searchFocused = false" class="absolute right-3 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Filters -->
                        <select x-show="!searchFocused" wire:model.live="locationFilter" class="flex-shrink-0 h-full py-0 pl-3 pr-8 rounded-full border border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs sm:text-sm text-gray-600 bg-white cursor-pointer w-auto transition-all">
                            <option value="All">All Locations</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Worldwide">Worldwide</option>
                        </select>
                        
                        <select x-show="!searchFocused" wire:model.live="provinceFilter" class="flex-shrink-0 h-full py-0 pl-3 pr-8 rounded-full border border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs sm:text-sm text-gray-600 bg-white cursor-pointer w-auto transition-all">
                            <option value="All">Semua Provinsi</option>
                            @foreach($provinces as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>

                        <select x-show="!searchFocused" wire:model.live="ageFilter" class="flex-shrink-0 h-full py-0 pl-3 pr-8 rounded-full border border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs sm:text-sm text-gray-600 bg-white cursor-pointer w-auto transition-all">
                            <option value="All">Semua Usia</option>
                            @for($i = 18; $i <= 45; $i++)
                                <option value="{{ $i }}">{{ $i }} Tahun</option>
                            @endfor
                        </select>

                        <select x-show="!searchFocused" wire:model.live="dateFilter" class="flex-shrink-0 h-full py-0 pl-3 pr-8 rounded-full border border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs sm:text-sm text-gray-600 bg-white cursor-pointer w-auto transition-all">
                            <option value="All">Any Time</option>
                            <option value="Past 24 Hours">Past 24 Hours</option>
                            <option value="Past Week">Past Week</option>
                            <option value="Past Month">Past Month</option>
                        </select>

                        <select x-show="!searchFocused" wire:model.live="salaryFilter" class="flex-shrink-0 h-full py-0 pl-3 pr-8 rounded-full border border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs sm:text-sm text-gray-600 bg-white cursor-pointer w-auto transition-all">
                            <option value="All">Semua Gaji</option>
                            <option value="< 3 Juta">< 3 Juta</option>
                            <option value="3 - 5 Juta">3 - 5 Juta</option>
                            <option value="5 - 10 Juta">5 - 10 Juta</option>
                            <option value="> 10 Juta">> 10 Juta</option>
                        </select>

                        @auth
                            <label x-show="!searchFocused" class="flex items-center gap-2 flex-shrink-0 h-full py-1.5 pl-3 pr-4 rounded-full border shadow-sm cursor-pointer transition-all text-xs sm:text-sm" :class="$wire.matchSkills ? 'bg-red-50 border-red-300 text-red-700' : 'bg-white border-gray-300 text-gray-600'">
                                <input type="checkbox" wire:model.live="matchSkills" class="rounded text-red-600 focus:ring-red-500 border-gray-300">
                                <span class="font-medium whitespace-nowrap">Sesuai Skill Saya</span>
                            </label>
                        @endauth
                    </div>

                    <!-- Right Arrow Button (Desktop Only) -->
                    <button type="button" x-show="canScrollRight" @click="$refs.filterScroll.scrollBy({ left: 120, behavior: 'smooth' })" style="right: -0.5rem;" class="hidden lg:flex absolute z-20 bg-white/95 hover:bg-white border border-gray-200 shadow-md rounded-full p-1 text-gray-500 hover:text-red-600 transition-all items-center justify-center w-7 h-7">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <!-- Right Gradient Overlay (Mobile/Tablet) -->
                    <div x-show="canScrollRight" class="lg:hidden absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>
                </div>
            </div>
        </div>

        <!-- Platform Filters (Sticky child bar) -->
        <div class="border-t border-gray-100 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 md:py-3">
                <div x-data="{ 
                    canScrollLeft: false, 
                    canScrollRight: false,
                    checkScroll() {
                        const el = $refs.platformScroll;
                        if (!el) return;
                        this.canScrollLeft = el.scrollLeft > 5;
                        this.canScrollRight = el.scrollWidth - el.clientWidth - el.scrollLeft > 5;
                    }
                }" x-init="setTimeout(() => checkScroll(), 200)" @resize.window.debounce.100ms="checkScroll()" class="relative flex items-center w-full">
                    
                    <!-- Left Gradient Overlay (Mobile/Tablet) -->
                    <div x-show="canScrollLeft" class="lg:hidden absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>

                    <!-- Left Arrow Button (Desktop Only) -->
                    <button type="button" x-show="canScrollLeft" @click="$refs.platformScroll.scrollBy({ left: -200, behavior: 'smooth' })" style="left: -0.75rem;" class="hidden lg:flex absolute z-20 bg-white/95 hover:bg-white border border-gray-200 shadow-md rounded-full p-1.5 text-gray-500 hover:text-red-600 transition-all items-center justify-center w-9 h-9">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <!-- Scrollable platform list -->
                    <div x-ref="platformScroll" @scroll="checkScroll()" class="flex gap-3 overflow-x-auto pb-1 scrollbar-hide w-full -mx-4 px-4 lg:mx-0 lg:px-0">
                        <button wire:click="selectPlatform(null)" class="flex-shrink-0 whitespace-nowrap px-6 py-2.5 rounded-full text-sm font-medium transition-all {{ $selectedPlatform === null ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                            All Platforms
                        </button>
                        @foreach($platforms as $platform)
                            <button wire:click="selectPlatform({{ $platform->id }})" class="flex-shrink-0 whitespace-nowrap flex items-center gap-3 px-5 py-2.5 rounded-full text-sm font-medium transition-all {{ $selectedPlatform === $platform->id ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                                @if($platform->icon_path)
                                    <img src="{{ $platform->icon_path }}" class="w-5 h-5 rounded-md object-cover flex-shrink-0" alt="{{ $platform->name }}">
                                @endif
                                <span>{{ $platform->name }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Right Arrow Button (Desktop Only) -->
                    <button type="button" x-show="canScrollRight" @click="$refs.platformScroll.scrollBy({ left: 200, behavior: 'smooth' })" style="right: -0.75rem;" class="hidden lg:flex absolute z-20 bg-white/95 hover:bg-white border border-gray-200 shadow-md rounded-full p-1.5 text-gray-500 hover:text-red-600 transition-all items-center justify-center w-9 h-9">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>

                    <!-- Right Gradient Overlay (Mobile/Tablet) -->
                    <div x-show="canScrollRight" class="lg:hidden absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-24 md:pb-8 w-full flex gap-8">
        
        <!-- Left Column: Main Feed -->
        <div class="flex-1 transition-all duration-300 ease-in-out {{ $selectedJob ? 'lg:w-1/2' : 'w-full' }}">

            <!-- Dynamic Notification Pill (Twitter style) -->
            @if($newJobsCount > 0)
                <div class="flex justify-center mb-6 sticky top-20 z-20 animate-fade-in-up">
                    <button wire:click="showNewJobs" class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg font-extrabold text-xs sm:text-sm transition-all transform hover:scale-[1.03] active:scale-95 border border-red-500/20 backdrop-blur-md bg-opacity-95">
                        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        <span>Lihat {{ $newJobsCount }} lowongan baru</span>
                    </button>
                </div>
            @endif

            <div class="flex items-center justify-between mb-4 px-1">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    Ditemukan <span class="text-red-600 font-extrabold">{{ number_format($totalJobs) }}</span> lowongan kerja
                </span>
            </div>

            <!-- Job Cards -->
            <div class="space-y-4">
                @forelse($jobs as $job)
                    <div wire:click="selectJob({{ $job->id }})" class="group cursor-pointer bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md hover:border-red-100 transition-all duration-200 transform hover:-translate-y-1 {{ $selectedJob && $selectedJob->id === $job->id ? 'ring-2 ring-red-500 border-transparent' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ $job->company_logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($job->company_name ?? 'Confidential') . '&color=4f46e5&background=e0e7ff&size=64&bold=true' }}" class="w-12 h-12 rounded-xl object-cover border border-gray-100 shadow-sm" alt="{{ $job->company_name }}">
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-red-600 transition-colors">{{ $job->job_title }}</h3>
                                    </div>
                                    <p class="text-gray-500 mt-1 font-medium">{{ $job->company_name ?? 'Confidential Company' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2.5 py-1 rounded-lg">
                                    {{ ($job->posted_at ?? $job->created_at)->diffForHumans() }}
                                </span>
                                <button wire:click.stop="toggleSaveJob({{ $job->id }})" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-red-600 transition-colors" title="Simpan Lowongan">
                                    <svg class="w-5 h-5 {{ auth()->check() && $job->savedByUsers->contains(auth()->id()) ? 'fill-red-600 text-red-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                    {{ $job->platform->name }}
                                </span>
                                @if($job->location)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700" title="Lokasi">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $job->location }}
                                </span>
                                @endif
                                @if($job->province)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700" title="Provinsi">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $job->province }}
                                </span>
                                @endif
                                @if($job->min_age || $job->max_age)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700" title="Persyaratan Usia">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    @if($job->min_age && $job->max_age)
                                        {{ $job->min_age }}-{{ $job->max_age }} Thn
                                    @elseif($job->min_age)
                                        Min {{ $job->min_age }} Thn
                                    @else
                                        Maks {{ $job->max_age }} Thn
                                    @endif
                                </span>
                                @endif
                                @if($job->min_salary || $job->max_salary)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700" title="Gaji">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @if($job->min_salary && $job->max_salary)
                                        {{ $job->min_salary >= 1000000 ? number_format($job->min_salary/1000000, 0, ',', '.') . 'jt' : number_format($job->min_salary, 0, ',', '.') }} - 
                                        {{ $job->max_salary >= 1000000 ? number_format($job->max_salary/1000000, 0, ',', '.') . 'jt' : number_format($job->max_salary, 0, ',', '.') }}
                                    @elseif($job->min_salary)
                                        Min {{ $job->min_salary >= 1000000 ? number_format($job->min_salary/1000000, 0, ',', '.') . 'jt' : number_format($job->min_salary, 0, ',', '.') }}
                                    @else
                                        Maks {{ $job->max_salary >= 1000000 ? number_format($job->max_salary/1000000, 0, ',', '.') . 'jt' : number_format($job->max_salary, 0, ',', '.') }}
                                    @endif
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white rounded-2xl border border-gray-100 border-dashed">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or search query.</p>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6 flex justify-center pb-8">
                @if(count($jobs) >= $perPage && count($jobs) < $totalJobs)
                    <div x-intersect="$wire.loadMore()" class="py-4 flex justify-center w-full">
                        <div class="flex flex-col items-center justify-center gap-3">
                            <div class="relative w-10 h-10 flex items-center justify-center">
                                <div class="absolute inset-0 bg-red-500 rounded-xl opacity-20 animate-ping"></div>
                                <div class="relative w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-red-500/40 animate-bounce">
                                    MP
                                </div>
                            </div>
                            <span class="text-xs font-bold text-red-600 animate-pulse tracking-widest uppercase">Memuat...</span>
                        </div>
                    </div>
                @elseif(count($jobs) > 0 && count($jobs) >= $totalJobs)
                    <div class="py-8 flex flex-col items-center justify-center w-full text-center">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900">Itu saja untuk saat ini!</h4>
                        <p class="text-xs text-gray-500 mt-1">Anda sudah melihat semua lowongan yang tersedia.</p>
                    </div>
                @endif
            </div>

            <!-- Visitor Tracker -->
            <div class="mt-4 pt-6 border-t border-gray-100 flex flex-col items-center justify-center text-center">
                @php
                    $todayVisitors = 0;
                    $totalVisitors = 0;
                    try {
                        $todayVisitors = \Illuminate\Support\Facades\DB::table('visitors')->where('visit_date', now()->toDateString())->count();
                        $totalVisitors = \Illuminate\Support\Facades\DB::table('visitors')->count();
                    } catch (\Exception $e) {}
                @endphp
                <div class="inline-flex items-center gap-4 px-6 py-3 bg-white rounded-full shadow-sm border border-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span class="text-xs font-bold text-gray-700">Pengunjung Hari Ini: <span class="text-green-600">{{ number_format($todayVisitors) }}</span></span>
                    </div>
                    <div class="w-px h-4 bg-gray-200"></div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span class="text-xs font-bold text-gray-700">Total Keseluruhan: <span class="text-blue-600">{{ number_format($totalVisitors) }}</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Detail View Drawer/Sidebar -->
        @if($selectedJob)
            <div class="hidden lg:block w-1/2 bg-white rounded-3xl shadow-xl border border-gray-100 sticky top-24 h-[calc(100vh-8rem)] overflow-y-auto animate-fade-in-up">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex gap-5 items-center">
                            <img src="{{ $selectedJob->company_logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($selectedJob->company_name ?? 'Confidential') . '&color=4f46e5&background=e0e7ff&size=128&bold=true' }}" class="w-16 h-16 rounded-2xl object-cover border border-gray-100 shadow-sm" alt="{{ $selectedJob->company_name }}">
                            <div>
                                <h2 class="text-2xl font-extrabold text-gray-900">{{ $selectedJob->job_title }}</h2>
                                <p class="text-lg text-red-600 font-medium mt-1">{{ $selectedJob->company_name ?? 'Confidential Company' }}</p>
                            </div>
                        </div>
                        <button wire:click="closeJobDetails" class="p-2 text-gray-400 hover:text-gray-600 bg-gray-50 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mb-8">
                        @if($selectedJob->source_url)
                            <a href="{{ $selectedJob->source_url }}" target="_blank" class="px-3 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors text-sm font-semibold rounded-full flex items-center gap-1.5">
                                Buka di {{ $selectedJob->platform->name }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full">Source: {{ $selectedJob->platform->name }}</span>
                        @endif
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full">Posted: {{ ($selectedJob->posted_at ?? $selectedJob->created_at)->format('M d, Y') }}</span>
                        @if($selectedJob->province)
                            <span class="px-3 py-1 bg-red-50 text-red-700 text-sm font-semibold rounded-full">Provinsi: {{ $selectedJob->province }}</span>
                        @endif
                        @if($selectedJob->min_age || $selectedJob->max_age)
                            <span class="px-3 py-1 bg-amber-50 text-amber-700 text-sm font-semibold rounded-full">
                                Usia: 
                                @if($selectedJob->min_age && $selectedJob->max_age)
                                    {{ $selectedJob->min_age }}-{{ $selectedJob->max_age }} Tahun
                                @elseif($selectedJob->min_age)
                                    Minimal {{ $selectedJob->min_age }} Tahun
                                @else
                                    Maksimal {{ $selectedJob->max_age }} Tahun
                                @endif
                            </span>
                        @endif
                        @if($selectedJob->min_salary || $selectedJob->max_salary)
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-sm font-semibold rounded-full">
                                Gaji: 
                                @if($selectedJob->min_salary && $selectedJob->max_salary)
                                    {{ $selectedJob->min_salary >= 1000000 ? number_format($selectedJob->min_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->min_salary, 0, ',', '.') }} - 
                                    {{ $selectedJob->max_salary >= 1000000 ? number_format($selectedJob->max_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->max_salary, 0, ',', '.') }}
                                @elseif($selectedJob->min_salary)
                                    Minimal {{ $selectedJob->min_salary >= 1000000 ? number_format($selectedJob->min_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->min_salary, 0, ',', '.') }}
                                @else
                                    Maksimal {{ $selectedJob->max_salary >= 1000000 ? number_format($selectedJob->max_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->max_salary, 0, ',', '.') }}
                                @endif
                            </span>
                        @endif
                    </div>

                    <div class="prose prose-red max-w-none">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">AI Extracted Details</h3>
                        @if($selectedJob->requirements)
                            <ul class="space-y-3">
                                @foreach(json_decode($selectedJob->requirements, true) ?? [] as $req)
                                    <li class="flex items-start">
                                        <svg class="h-6 w-6 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span class="text-gray-700 leading-relaxed">{{ $req }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 italic">No structured details available yet.</p>
                        @endif
                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-100 flex items-center gap-3">
                        <button wire:click="toggleSaveJob({{ $selectedJob->id }})" class="flex-shrink-0 p-3.5 rounded-xl border border-gray-200 hover:border-red-200 text-gray-400 hover:text-red-600 hover:bg-red-50/20 transition-all" title="{{ auth()->check() && $selectedJob->savedByUsers->contains(auth()->id()) ? 'Hapus dari Simpanan' : 'Simpan Lowongan' }}">
                            <svg class="w-6 h-6 {{ auth()->check() && $selectedJob->savedByUsers->contains(auth()->id()) ? 'fill-red-600 text-red-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </button>
                        <a href="{{ $selectedJob->source_url ?? '#' }}" target="_blank" class="flex-1 flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-red-600 hover:bg-red-700 transition-all transform active:scale-95">
                            Lamar Sekarang
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Bottom Sheet (Visible only on small screens) -->
            <div class="fixed inset-0 z-[60] lg:hidden overflow-hidden flex flex-col justify-end {{ $selectedJob ? 'block' : 'hidden' }}">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeJobDetails" @touchmove.prevent></div>
                <div class="relative w-full max-w-md mx-auto bg-white rounded-t-[2.5rem] shadow-2xl flex flex-col h-[75vh] max-h-[75vh] animate-slide-up mt-auto border-t border-gray-100 overflow-hidden overscroll-contain z-10">
                    <!-- Pull Indicator -->
                    <div class="w-full flex justify-center pt-4 pb-2 cursor-pointer" wire:click="closeJobDetails" @touchmove.prevent>
                        <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
                    </div>
                    
                    <!-- Header: Unified Logo and Job Title -->
                    <div class="px-6 pb-4 flex items-start justify-between gap-4 border-b border-gray-50" @touchmove.prevent>
                        <div class="flex gap-4 items-center flex-1 min-w-0">
                            <img src="{{ $selectedJob->company_logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($selectedJob->company_name ?? 'Confidential') . '&color=4f46e5&background=e0e7ff&size=64&bold=true' }}" class="w-12 h-12 rounded-2xl object-cover border border-gray-100 shadow-sm flex-shrink-0" alt="{{ $selectedJob->company_name }}">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-lg font-bold text-gray-900 leading-snug line-clamp-1">{{ $selectedJob->job_title }}</h2>
                                <div class="flex items-center gap-2 mt-0.5 min-w-0">
                                    <p class="text-sm text-red-600 font-semibold truncate flex-1 min-w-0">{{ $selectedJob->company_name ?? 'Confidential' }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700 border border-red-100 flex-shrink-0">
                                        {{ $selectedJob->platform->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button wire:click="closeJobDetails" class="flex-shrink-0 h-8 w-8 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Content: Info & Requirements (Scrollable) -->
                    <div class="flex-1 overflow-y-auto overscroll-contain overflow-x-hidden p-6 space-y-5">
                        <!-- Quick Meta Badges -->
                        <div class="flex flex-wrap gap-2">
                            @if($selectedJob->location)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-100">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $selectedJob->location }}
                                </span>
                            @endif
                            @if($selectedJob->province)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $selectedJob->province }}
                                </span>
                            @endif
                            @if($selectedJob->min_age || $selectedJob->max_age)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    @if($selectedJob->min_age && $selectedJob->max_age)
                                        {{ $selectedJob->min_age }}-{{ $selectedJob->max_age }} Thn
                                    @elseif($selectedJob->min_age)
                                        Min {{ $selectedJob->min_age }} Thn
                                    @else
                                        Maks {{ $selectedJob->max_age }} Thn
                                    @endif
                                </span>
                            @endif
                            @if($selectedJob->min_salary || $selectedJob->max_salary)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @if($selectedJob->min_salary && $selectedJob->max_salary)
                                        {{ $selectedJob->min_salary >= 1000000 ? number_format($selectedJob->min_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->min_salary, 0, ',', '.') }} - 
                                        {{ $selectedJob->max_salary >= 1000000 ? number_format($selectedJob->max_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->max_salary, 0, ',', '.') }}
                                    @elseif($selectedJob->min_salary)
                                        Min {{ $selectedJob->min_salary >= 1000000 ? number_format($selectedJob->min_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->min_salary, 0, ',', '.') }}
                                    @else
                                        Maks {{ $selectedJob->max_salary >= 1000000 ? number_format($selectedJob->max_salary/1000000, 0, ',', '.') . 'jt' : number_format($selectedJob->max_salary, 0, ',', '.') }}
                                    @endif
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-100">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ ($selectedJob->posted_at ?? $selectedJob->created_at)->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Requirements Section -->
                        <div class="space-y-3">
                            <h3 class="text-xs font-extrabold text-gray-400 uppercase tracking-wider">Kualifikasi & Persyaratan</h3>
                            @if($selectedJob->requirements)
                                <ul class="space-y-3">
                                    @foreach(json_decode($selectedJob->requirements, true) ?? [] as $req)
                                        <li class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center mt-0.5 border border-emerald-100">
                                                <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <span class="text-sm text-gray-600 leading-relaxed">{{ $req }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-400 italic">Tidak ada rincian kualifikasi khusus.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Sticky Footer: CTA Button -->
                    <div class="p-6 border-t border-gray-100 bg-white/95 backdrop-blur-sm pb-8 flex-shrink-0 flex items-center gap-3" @touchmove.prevent>
                        <button wire:click="toggleSaveJob({{ $selectedJob->id }})" class="flex-shrink-0 p-4 rounded-2xl border border-gray-200 hover:border-red-200 text-gray-400 hover:text-red-600 active:scale-95 transition-all" title="{{ auth()->check() && $selectedJob->savedByUsers->contains(auth()->id()) ? 'Hapus dari Simpanan' : 'Simpan Lowongan' }}">
                            <svg class="w-6 h-6 {{ auth()->check() && $selectedJob->savedByUsers->contains(auth()->id()) ? 'fill-red-600 text-red-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </button>
                        <a href="{{ $selectedJob->source_url ?? '#' }}" target="_blank" class="flex-1 flex justify-center items-center gap-2 py-4 px-6 border border-transparent rounded-2xl shadow-md text-sm font-bold text-white bg-red-600 hover:bg-red-700 active:scale-[0.98] transition-all">
                            Lamar Sekarang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </main>
    
    <!-- Mobile Bottom Navigation -->
    <div class="md:hidden fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 flex justify-around items-center px-4 shadow-lg">
        <button @click="searchFocused = false" wire:click="$set('showSavedOnly', false)" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 transition-colors" :class="(!searchFocused && !@json($showSavedOnly)) ? 'text-red-600' : 'text-gray-500 group-hover:text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-[10px] font-medium transition-colors" :class="(!searchFocused && !@json($showSavedOnly)) ? 'text-red-600' : 'text-gray-500 group-hover:text-red-600'">Home</span>
        </button>
        <button type="button" @click="searchFocused = true; window.scrollTo({top: 0, behavior: 'smooth'}); setTimeout(() => $refs.searchInput.focus(), 100)" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 transition-colors" :class="searchFocused ? 'text-red-600' : 'text-gray-500 group-hover:text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <span class="text-[10px] font-medium transition-colors" :class="searchFocused ? 'text-red-600' : 'text-gray-500 group-hover:text-red-600'">Search</span>
        </button>
        <button @click="searchFocused = false" wire:click="toggleSavedFilter" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 transition-colors {{ $showSavedOnly ? 'text-red-600 fill-red-600' : 'text-gray-500 group-hover:text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
            <span class="text-[10px] font-medium transition-colors {{ $showSavedOnly ? 'text-red-600' : 'text-gray-500 group-hover:text-red-600' }}">Saved</span>
        </button>
        <a href="{{ route('profile') }}" class="flex flex-col items-center justify-center w-full group">
            <svg class="w-6 h-6 mb-1 text-gray-500 group-hover:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-[10px] font-medium text-gray-500 group-hover:text-red-600 transition-colors">Profile</span>
        </a>
    </div>

    
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .animate-slide-up { animation: slideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</div>
