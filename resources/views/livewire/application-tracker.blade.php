<div class="py-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Lamaran Saya</h1>
                <p class="mt-2 text-sm text-gray-600">Lacak progres lamaran kerja Anda dengan Kanban board ini.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Cari Loker Lagi
                </a>
            </div>
        </div>

        @php
            $columns = [
                'saved' => ['title' => 'Disimpan', 'color' => 'bg-gray-100', 'border' => 'border-gray-200', 'text' => 'text-gray-800'],
                'applied' => ['title' => 'Sudah Dilamar', 'color' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800'],
                'interviewing' => ['title' => 'Panggilan Interview', 'color' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-800'],
                'accepted' => ['title' => 'Diterima', 'color' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-800'],
                'rejected' => ['title' => 'Ditolak', 'color' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-800'],
            ];
        @endphp

        <!-- Kanban Board Container -->
        <div class="flex overflow-x-auto pb-8 space-x-6">
            @foreach($columns as $status => $column)
            <div class="flex-shrink-0 w-80 flex flex-col rounded-xl bg-white border border-gray-200 shadow-sm overflow-hidden h-full max-h-[75vh]">
                <!-- Column Header -->
                <div class="px-4 py-3 border-b {{ $column['border'] }} {{ $column['color'] }} flex justify-between items-center sticky top-0 z-10">
                    <h3 class="font-semibold text-sm uppercase tracking-wider {{ $column['text'] }}">
                        {{ $column['title'] }}
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $column['color'] }} {{ $column['text'] }} border {{ $column['border'] }}">
                        {{ count($applications[$status]) }}
                    </span>
                </div>

                <!-- Column Body -->
                <div class="p-3 flex-1 overflow-y-auto space-y-3 bg-gray-50/50 min-h-[150px]">
                    @forelse($applications[$status] as $job)
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition group relative flex flex-col gap-3">
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 leading-tight">
                                    <a href="{{ $job->source_url }}" target="_blank" class="hover:text-indigo-600 hover:underline">
                                        {{ $job->job_title }}
                                    </a>
                                </h4>
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ $job->company_name }}
                                </p>
                            </div>
                            <!-- Platform Badge -->
                            @if($job->platform)
                                <img src="{{ $job->platform->logo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($job->platform->name).'&background=random' }}" 
                                     alt="{{ $job->platform->name }}" 
                                     class="w-6 h-6 rounded-full object-cover flex-shrink-0"
                                     title="{{ $job->platform->name }}">
                            @endif
                        </div>
                        
                        <div class="flex items-center text-xs text-gray-500 gap-2">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ \Illuminate\Support\Str::limit($job->location, 15) }}
                            </span>
                        </div>

                        <div class="pt-3 mt-auto border-t border-gray-100 flex justify-between items-center">
                            <!-- Dropdown for moving status -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="text-xs inline-flex items-center gap-1 text-gray-600 hover:text-indigo-600 font-medium transition">
                                    Pindahkan
                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition.opacity class="absolute left-0 mt-1 w-40 bg-white rounded-md shadow-lg border border-gray-200 z-20 py-1">
                                    @foreach($columns as $targetStatus => $targetColumn)
                                        @if($targetStatus !== $status)
                                        <button wire:click="updateStatus({{ $job->id }}, '{{ $targetStatus }}')" class="w-full text-left px-4 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-indigo-600 transition">
                                            Ke: {{ $targetColumn['title'] }}
                                        </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            
                            <button wire:click="deleteJob({{ $job->id }})" wire:confirm="Hapus lamaran ini dari pelacakan?" class="text-gray-400 hover:text-red-600 transition p-1" title="Hapus">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-4 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50/50">
                        <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-xs font-medium text-gray-500">Belum ada loker</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
        
    </div>
</div>

