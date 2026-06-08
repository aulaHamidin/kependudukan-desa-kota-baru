<x-app-layout>
    {{-- Welcome Hero Banner --}}
    <div class="rounded-xl p-6 sm:p-8 shadow-lg mb-6 relative overflow-hidden border border-blue-100"
        style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(37, 99, 235, 0.12) 60%, rgba(29, 78, 216, 0.15) 100%);">
        {{-- Decorative circles --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-500/5 rounded-full"></div>
        <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-blue-500/5 rounded-full"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl text-gray-800 font-bold mb-1">Selamat datang, {{ Auth::user()->name }}!</h1>
                <p class="text-gray-600 text-sm">
                    {{ now()->translatedFormat('l, d F Y') }} &mdash; SIAK-Desa Dashboard
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-lg px-4 py-2.5 text-center border border-blue-200 shadow-sm">
                    <p class="text-[10px] uppercase tracking-wider text-gray-500 font-medium">Penduduk</p>
                    <p class="text-lg font-extrabold text-gray-900">
                        {{ number_format($widgets['active_penduduk_count']) }}</p>
                </div>
                @can('viewAny', App\Models\SuratTerbit::class)
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-lg px-4 py-2.5 text-center border border-blue-200 shadow-sm">
                    <p class="text-[10px] uppercase tracking-wider text-gray-500 font-medium">Surat Aktif</p>
                    <p class="text-lg font-extrabold text-gray-900">
                        {{ number_format($widgets['surat_stats']['total_aktif']) }}</p>
                </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- Primary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card value="{{ number_format($pendudukStats['aktif'] ?? 0) }}" label="Penduduk Aktif" icon="users"
            color="primary" />
        <x-stat-card value="{{ number_format($kkStats['aktif'] ?? 0) }}" label="KK Aktif" icon="home"
            color="indigo" />
        <x-stat-card value="{{ $widgets['event_stats']['total'] ?? 0 }}" label="Event Tahun Ini" icon="calendar"
            color="emerald" />
        @can('viewAny', App\Models\SuratTerbit::class)
            <x-stat-card value="{{ $widgets['surat_stats']['bulan_ini'] ?? 0 }}" label="Surat Bulan Ini" icon="document"
                color="amber" />
        @endcan
    </div>

    {{-- Event Breakdown Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Rincian Event Tahun {{ date('Y') }}</h3>
            </div>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="relative bg-gradient-to-br from-emerald-50 to-emerald-100/60 rounded-xl p-4 border border-emerald-100 group hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-9 h-9 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Kelahiran</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $widgets['event_stats']['kelahiran'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">event tercatat</p>
                </div>

                <div
                    class="relative bg-gradient-to-br from-gray-50 to-gray-100/60 rounded-xl p-4 border border-gray-200 group hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-9 h-9 rounded-lg bg-gray-500 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Kematian</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $widgets['event_stats']['kematian'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">event tercatat</p>
                </div>

                <div
                    class="relative bg-gradient-to-br from-amber-50 to-amber-100/60 rounded-xl p-4 border border-amber-100 group hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-9 h-9 rounded-lg bg-amber-500 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Pindah</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $widgets['event_stats']['pindah'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">event tercatat</p>
                </div>

                <div
                    class="relative bg-gradient-to-br from-blue-50 to-blue-100/60 rounded-xl p-4 border border-blue-100 group hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-9 h-9 rounded-lg bg-blue-500 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Datang</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-800">{{ $widgets['event_stats']['datang'] ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">event tercatat</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Section: Chart & Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Chart: Events by Month (2 cols) --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden h-full flex flex-col">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Grafik Event {{ date('Y') }}</h3>
                </div>
                <div class="p-5 flex-1 flex items-center">
                    <div class="w-full" style="height: 320px;">
                        <canvas id="eventsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions (1 col) --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden h-full flex flex-col">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Aksi Cepat</h3>
                </div>
                <div class="p-5 flex-1 flex items-center">
                    <div class="space-y-2 w-full">
                        @can('create', App\Models\Event::class)
                            <a href="{{ route('events.kelahiran.create') }}"
                                class="group flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 hover:border-emerald-200 hover:shadow-md transition-all no-underline">
                                <div
                                    class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-emerald-700">Kelahiran</p>
                                    <p class="text-xs text-gray-500">Catat event kelahiran baru</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-600 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <a href="{{ route('events.kematian.create') }}"
                                class="group flex items-center gap-3 p-3.5 rounded-xl bg-rose-50 border border-rose-100 hover:bg-rose-100 hover:border-rose-200 hover:shadow-md transition-all no-underline">
                                <div
                                    class="w-10 h-10 rounded-lg bg-rose-500 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-rose-700">Kematian</p>
                                    <p class="text-xs text-gray-500">Catat event kematian</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-600 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <a href="{{ route('events.pindah.create') }}"
                                class="group flex items-center gap-3 p-3.5 rounded-xl bg-amber-50 border border-amber-100 hover:bg-amber-100 hover:border-amber-200 hover:shadow-md transition-all no-underline">
                                <div
                                    class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-amber-700">Pindah</p>
                                    <p class="text-xs text-gray-500">Catat event pindah keluar</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-600 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <a href="{{ route('events.datang.create') }}"
                                class="group flex items-center gap-3 p-3.5 rounded-xl bg-blue-50 border border-blue-100 hover:bg-blue-100 hover:border-blue-200 hover:shadow-md transition-all no-underline">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-blue-700">Datang</p>
                                    <p class="text-xs text-gray-500">Catat event pendatang baru</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endcan

                        @can('create', App\Models\SuratTerbit::class)
                            <a href="{{ route('surat.terbit.create') }}"
                                class="group flex items-center gap-3 p-3.5 rounded-xl bg-purple-50 border border-purple-100 hover:bg-purple-100 hover:border-purple-200 hover:shadow-md transition-all no-underline">
                                <div
                                    class="w-10 h-10 rounded-lg bg-purple-500 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-700 group-hover:text-purple-700">Buat Surat</p>
                                    <p class="text-xs text-gray-500">Terbitkan surat keterangan</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endcan

                        @cannot('create', App\Models\Event::class)
                            <div class="flex flex-col items-center justify-center py-6 px-4 rounded-xl bg-blue-50 border border-blue-100 text-center">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-500 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-blue-700">Mode Monitoring</p>
                                <p class="text-[11px] text-blue-400 mt-1 leading-relaxed">Akun Anda hanya dapat melihat data, tidak dapat membuat atau mengubah</p>
                            </div>
                        @endcannot
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column (2 cols) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Recent Events --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">Event Terbaru</h3>
                    </div>
                    <a href="{{ route('approvals.index') }}"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-700">Lihat Semua &rarr;</a>
                </div>
                <div class="p-5 space-y-2">
                    @forelse($widgets['recent_events'] as $event)
                        @php
                            $eventColors = [
                                'KELAHIRAN' => [
                                    'bg' => 'bg-emerald-50',
                                    'text' => 'text-emerald-600',
                                    'border' => 'border-emerald-100',
                                ],
                                'KEMATIAN' => [
                                    'bg' => 'bg-gray-100',
                                    'text' => 'text-gray-600',
                                    'border' => 'border-gray-200',
                                ],
                                'PINDAH' => [
                                    'bg' => 'bg-amber-50',
                                    'text' => 'text-amber-600',
                                    'border' => 'border-amber-100',
                                ],
                                'DATANG' => [
                                    'bg' => 'bg-blue-50',
                                    'text' => 'text-blue-600',
                                    'border' => 'border-blue-100',
                                ],
                            ];
                            $colors = $eventColors[$event->event_type_code] ?? [
                                'bg' => 'bg-gray-50',
                                'text' => 'text-gray-600',
                                'border' => 'border-gray-100',
                            ];
                        @endphp
                        <div
                            class="flex items-center gap-3 p-3 rounded-lg {{ $colors['bg'] }} border {{ $colors['border'] }} hover:shadow-sm transition-all">
                            <div
                                class="w-9 h-9 rounded-lg bg-white/80 {{ $colors['text'] }} flex items-center justify-center shrink-0 shadow-sm">
                                @switch($event->event_type_code)
                                    @case('KELAHIRAN')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    @break

                                    @case('KEMATIAN')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                        </svg>
                                    @break

                                    @case('PINDAH')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    @break

                                    @case('DATANG')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                        </svg>
                                    @break
                                @endswitch
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-gray-700">
                                        {{ $event->eventType->nama ?? $event->event_type_code }}
                                    </p>
                                    <span
                                        class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $colors['bg'] }} {{ $colors['text'] }}">
                                        {{ $event->event_type_code }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $event->penduduk->nama_lengkap ?? 'N/A' }} &middot;
                                    {{ $event->event_date->format('d M Y') }}
                                </p>
                            </div>
                            <span class="text-[11px] text-gray-400 whitespace-nowrap">
                                {{ $event->verified_at?->diffForHumans() ?? $event->created_at->diffForHumans() }}
                            </span>
                        </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <p class="text-sm text-gray-400">Belum ada event terbaru</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Surat --}}
                @can('viewAny', App\Models\SuratTerbit::class)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700">Surat Terbaru</h3>
                        </div>
                        <a href="{{ route('surat.terbit.index') }}"
                            class="text-xs font-semibold text-primary-600 hover:text-primary-700">Lihat Semua &rarr;</a>
                    </div>
                    <div class="p-5 space-y-2">
                        @forelse($widgets['recent_surat'] as $surat)
                            <div
                                class="flex items-center gap-3 p-3 rounded-lg bg-amber-50/50 border border-amber-100 hover:shadow-sm transition-all">
                                <div
                                    class="w-9 h-9 rounded-lg bg-white/80 text-amber-600 flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-700 truncate">
                                        {{ $surat->jenisSurat->nama ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $surat->penduduk->nama_lengkap ?? 'N/A' }} &middot; <span
                                            class="font-mono text-[11px]">{{ $surat->nomor_surat }}</span>
                                    </p>
                                </div>
                                <span class="text-[11px] text-gray-400 whitespace-nowrap">
                                    {{ $surat->tanggal_terbit->format('d M') }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <p class="text-sm text-gray-400">Belum ada surat terbaru</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                @endcan
            </div>

            {{-- Right Column (1 col) --}}
            <div class="space-y-6">
                {{-- Pending Events --}}
                @if ($widgets['pending_events']->count() > 0)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-700">Menunggu Approval</h3>
                            </div>
                            <span class="text-[10px] font-bold bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full">
                                {{ $widgets['pending_events']->count() }}
                            </span>
                        </div>
                        <div class="p-4 space-y-2">
                            @foreach ($widgets['pending_events'] as $event)
                                <a href="{{ route('approvals.index') }}"
                                    class="flex items-center gap-3 p-3 rounded-lg bg-amber-50/50 border border-amber-100 hover:bg-amber-50 hover:shadow-sm transition-all cursor-pointer no-underline">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-700 truncate">
                                            {{ $event->eventType->nama ?? $event->event_type_code }}
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            {{ $event->penduduk->nama_lengkap ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                        Pending
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40 text-center">
                            <a href="{{ route('approvals.index') }}"
                                class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                                Lihat semua approval &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Surat Expiring Soon --}}
                @can('viewAny', App\Models\SuratTerbit::class)
                @if ($widgets['expiring_surat']->count() > 0)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-700">Surat Akan Kadaluarsa</h3>
                            </div>
                            <span class="text-[10px] font-bold bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full">
                                {{ $widgets['expiring_surat']->count() }}
                            </span>
                        </div>
                        <div class="p-4 space-y-2">
                            @foreach ($widgets['expiring_surat']->take(5) as $surat)
                                <div
                                    class="flex items-center gap-3 p-3 rounded-lg {{ $surat->days_remaining <= 1 ? 'bg-rose-50 border border-rose-200' : 'bg-amber-50/50 border border-amber-100' }} transition-all">
                                    <div
                                        class="w-8 h-8 rounded-lg {{ $surat->days_remaining <= 1 ? 'bg-rose-500' : 'bg-amber-500' }} text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-700 truncate">{{ $surat->jenis_surat }}
                                        </p>
                                        <p class="text-[11px] text-gray-400">{{ $surat->nama_lengkap }}</p>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold {{ $surat->days_remaining <= 1 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }} px-2 py-0.5 rounded-full whitespace-nowrap">
                                        {{ $surat->days_remaining }} hari
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @endcan

                {{-- Data Inconsistencies --}}
                @if ($widgets['inconsistency_count'] > 0)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-semibold text-gray-700">Data Inkonsisten</h3>
                            </div>
                            <span class="text-[10px] font-bold bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full">
                                {{ $widgets['inconsistency_count'] }}
                            </span>
                        </div>
                        <div class="p-4 space-y-2">
                            @foreach ($widgets['data_inconsistencies']->take(5) as $issue)
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-rose-50 border border-rose-100">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-700">{{ $issue->nama_lengkap }}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $issue->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Chart: Penduduk by Age Group --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">Penduduk per Kelompok Umur</h3>
                    </div>
                    <div class="p-5">
                        <div class="h-52">
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Statistics Summary --}}
                <div class="rounded-xl p-5 text-white shadow-lg relative overflow-hidden"
                    style="background: linear-gradient(135deg, #003580 0%, #002a6b 60%, #001d4d 100%);">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-white/5 rounded-full"></div>

                    <div class="relative">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                            </div>
                            <span class="text-sm font-bold">Ringkasan</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-white/10 rounded-lg px-3 py-2.5">
                                <span class="text-xs text-blue-200">Total Penduduk Aktif</span>
                                <span
                                    class="text-sm font-extrabold">{{ number_format($widgets['active_penduduk_count']) }}</span>
                            </div>
                            @can('viewAny', App\Models\SuratTerbit::class)
                            <div class="flex items-center justify-between bg-white/10 rounded-lg px-3 py-2.5">
                                <span class="text-xs text-blue-200">Surat Aktif</span>
                                <span
                                    class="text-sm font-extrabold">{{ number_format($widgets['surat_stats']['total_aktif']) }}</span>
                            </div>
                            @endcan
                            <div class="flex items-center justify-between bg-white/10 rounded-lg px-3 py-2.5">
                                <span class="text-xs text-blue-200">Event Tahun Ini</span>
                                <span
                                    class="text-sm font-extrabold">{{ number_format($widgets['event_stats']['total']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                // Events by Month Chart
                const eventsCtx = document.getElementById('eventsChart');
                if (eventsCtx) {
                    new Chart(eventsCtx, {
                        type: 'line',
                        data: {
                            labels: @json($widgets['events_by_month']['labels']),
                            datasets: [{
                                label: 'Jumlah Event',
                                data: @json($widgets['events_by_month']['data']),
                                borderColor: 'rgb(79, 70, 229)',
                                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 2.5,
                                pointBackgroundColor: 'rgb(79, 70, 229)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgb(30, 27, 75)',
                                    titleFont: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 11
                                    },
                                    padding: 10,
                                    cornerRadius: 8,
                                    displayColors: false,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        color: 'rgba(0,0,0,0.04)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }

                // Age Group Chart
                const ageCtx = document.getElementById('ageChart');
                if (ageCtx) {
                    new Chart(ageCtx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($widgets['penduduk_by_age']['labels']),
                            datasets: [{
                                label: 'Jumlah Penduduk',
                                data: @json($widgets['penduduk_by_age']['data']),
                                backgroundColor: [
                                    'rgba(244, 114, 182, 0.85)', // <1  - pink (bayi)
                                    'rgba(251, 191, 36, 0.85)',  // 1-6 - kuning (balita)
                                    'rgba(52, 211, 153, 0.85)',  // 7-12 - hijau (anak)
                                    'rgba(96, 165, 250, 0.85)',  // 13-19 - biru muda (remaja)
                                    'rgba(79, 70, 229, 0.85)',   // 20-30 - indigo (dewasa muda)
                                    'rgba(139, 92, 246, 0.85)',  // 31-40 - ungu (dewasa)
                                    'rgba(245, 158, 11, 0.85)',  // 41-60 - amber (paruh baya)
                                    'rgba(148, 163, 184, 0.85)', // >60  - abu-abu (lansia)
                                ],
                                borderWidth: 0,
                                hoverOffset: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        padding: 12,
                                        font: {
                                            size: 10
                                        },
                                        usePointStyle: true,
                                        pointStyle: 'rectRounded',
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgb(30, 27, 75)',
                                    titleFont: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 11
                                    },
                                    padding: 10,
                                    cornerRadius: 8,
                                }
                            }
                        }
                    });
                }
            </script>
        @endpush
    </x-app-layout>
