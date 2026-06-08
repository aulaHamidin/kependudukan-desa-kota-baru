{{-- Data Peristiwa - Kelahiran Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Kelahiran', 'url' => route('events.kelahiran.index')],
            ['label' => 'Detail'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Detail Event Kelahiran" subtitle="Informasi lengkap kelahiran bayi">
            <x-slot name="actions">
                <div class="flex gap-2">
                    @can('update', $event)
                        <x-button variant="warning" icon="edit" :href="route('events.kelahiran.edit', $event)">
                            Edit
                        </x-button>
                    @endcan

                    @can('void', $event)
                        <x-button variant="danger" icon="ban" x-data=""
                            @click="$dispatch('open-modal', 'void-event-{{ $event->id }}')">
                            Batalkan Event
                        </x-button>
                    @endcan
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $kelahiran = $event->eventKelahiran;
        $statusGradient = match ($event->status_data) {
            'VERIFIED' => 'from-emerald-500 to-emerald-700',
            'DRAFT' => 'from-amber-500 to-amber-700',
            'VOID' => 'from-red-500 to-red-700',
            default => 'from-emerald-500 to-emerald-700',
        };
    @endphp

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-br {{ $statusGradient }} rounded-xl p-5 sm:p-6 text-white shadow-lg mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-6 h-6 text-white/80" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z" />
                    </svg>
                    <h2 class="text-xl font-bold">{{ $kelahiran->nama_bayi }}</h2>
                    @include('data_peristiwa.partials.status-badge', ['status' => $event->status_data])
                </div>
                <p class="text-white/70 text-sm">
                    Lahir pada {{ $event->event_date?->format('d F Y') ?? '-' }} &middot;
                    {{ $kelahiran->tempat_lahir ?? '-' }}
                </p>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-xs text-white/60 uppercase tracking-wide">ID Event</div>
                    <div class="text-lg font-mono font-bold">#{{ $event->id }}</div>
                </div>
            </div>
        </div>

        @if ($event->status_data === 'VOID')
            <div class="mt-4 bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                <h4 class="text-sm font-semibold text-white">Alasan Pembatalan</h4>
                <p class="text-sm text-white/80 mt-1">{{ $event->void_reason }}</p>
                <p class="text-xs text-white/60 mt-2">
                    Dibatalkan pada {{ $event->void_at?->format('d/m/Y H:i') }}
                    oleh {{ $event->voidedBy?->name ?? 'Sistem' }}
                </p>
            </div>
        @endif
    </div>

    {{-- Quick Info Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-pink-50 text-pink-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Status</div>
                    <div class="text-sm font-bold text-gray-800">
                        {{ $kelahiran->status_kelahiran->value === 'HIDUP' ? 'Lahir Hidup' : 'Lahir Mati' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jam Lahir</div>
                    <div class="text-sm font-bold text-gray-800">{{ $kelahiran->jam_lahir ?? '-' }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Berat</div>
                    <div class="text-sm font-bold text-gray-800">
                        {{ $kelahiran->berat_badan_kg ? $kelahiran->berat_badan_kg . ' kg' : '-' }}</div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Panjang</div>
                    <div class="text-sm font-bold text-gray-800">
                        {{ $kelahiran->panjang_badan_cm ? $kelahiran->panjang_badan_cm . ' cm' : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Kelahiran --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Informasi Kelahiran</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Kelahiran
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->event_date?->format('d F Y') ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jam Lahir</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $kelahiran->jam_lahir ?? '-' }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tempat Lahir</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $kelahiran->tempat_lahir ?? '-' }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Status Kelahiran
                            </dt>
                            <dd class="mt-1">
                                @if ($kelahiran->status_kelahiran->value === 'HIDUP')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">Hidup</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-gray-50 text-gray-700 border-gray-200">Lahir
                                        Mati</span>
                                @endif
                            </dd>
                        </div>
                        @if ($event->keterangan)
                            <div class="md:col-span-2 bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Keterangan
                                </dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ $event->keterangan }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Data Bayi --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Bayi</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">NIK</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                <span class="data-nik">{{ \App\Support\Masking::nik($event->penduduk->nik ?? '') }}</span>
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Bayi</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @if ($event->penduduk)
                                    <a href="{{ route('penduduk.show', $event->penduduk) }}"
                                        class="text-blue-600 hover:underline">{{ $kelahiran->nama_bayi }}</a>
                                @else
                                    {{ $kelahiran->nama_bayi }}
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jenis Kelamin
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kelahiran->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Agama</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->agama?->nama ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Anak Ke</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $kelahiran->anak_ke ?: '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Berat Badan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kelahiran->berat_badan_kg ? $kelahiran->berat_badan_kg . ' kg' : '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Panjang Badan
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kelahiran->panjang_badan_cm ? $kelahiran->panjang_badan_cm . ' cm' : '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Data Orang Tua --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Orang Tua</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Ayah</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @if ($kelahiran->ayah)
                                    <a href="{{ route('penduduk.show', $kelahiran->ayah) }}"
                                        class="text-blue-600 hover:underline">{{ $kelahiran->ayah->nama_lengkap }}</a>
                                @elseif ($kelahiran->nama_ayah)
                                    {{ $kelahiran->nama_ayah }} <span
                                        class="text-xs text-gray-500">(Non-penduduk)</span>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Ibu</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @if ($kelahiran->ibu)
                                    <a href="{{ route('penduduk.show', $kelahiran->ibu) }}"
                                        class="text-blue-600 hover:underline">{{ $kelahiran->ibu->nama_lengkap }}</a>
                                @elseif ($kelahiran->nama_ibu)
                                    {{ \App\Support\Masking::text($kelahiran->nama_ibu) }} <span
                                        class="text-xs text-gray-500">(Non-penduduk)</span>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        @if ($kelahiran->ayah)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">NIK Ayah</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-800"><span
                                        class="data-nik">{{ \App\Support\Masking::nik($kelahiran->ayah->nik) }}</span></dd>
                            </div>
                        @endif
                        @if ($kelahiran->ibu)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">NIK Ibu</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-800"><span
                                        class="data-nik">{{ \App\Support\Masking::nik($kelahiran->ibu->nik) }}</span></dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Data Kelahiran --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Kelahiran</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Penolong
                                Kelahiran</dt>
                            <dd class="mt-1 text-sm text-gray-800">
                                @if ($kelahiran->penolong_kelahiran)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst(strtolower($kelahiran->penolong_kelahiran)) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Penolong
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kelahiran->nama_penolong ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Kartu Keluarga Tujuan --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Kartu Keluarga Tujuan</h3>
                </div>
                <div class="p-5">
                    @if ($kelahiran->kkTujuan)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <a href="{{ route('kartu-keluarga.show', $kelahiran->kkTujuan) }}"
                                class="text-blue-600 hover:underline font-semibold text-sm">
                                {{ \App\Support\Masking::nik($kelahiran->kkTujuan->no_kk) }}
                            </a>
                            <p class="text-xs text-gray-500 mt-1">
                                @if ($kelahiran->kkTujuan->kepalaKeluarga && $kelahiran->kkTujuan->kepalaKeluarga->penduduk)
                                    Kepala Keluarga: {{ $kelahiran->kkTujuan->kepalaKeluarga->penduduk->nama_lengkap }}
                                @else
                                    Kepala Keluarga: -
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                RT {{ $event->rt->nomor_rt }} / RW {{ $event->rt->rw->nomor_rw }}
                            </p>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">Tidak terdaftar dalam KK</p>
                    @endif

                    @if ($kelahiran->status_kelahiran->value === 'MATI')
                        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <p class="text-sm text-amber-700">Bayi lahir mati tidak ditambahkan ke dalam KK</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Informasi Wilayah --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Wilayah</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">RT / RW</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-800">
                            @if ($event->rt && $event->rt->rw)
                                RT {{ $event->rt->nomor_rt }} / RW {{ $event->rt->rw->nomor_rw }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Desa</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-800">
                            @if ($event->rt && $event->rt->rw && $event->rt->rw->desa)
                                {{ $event->rt->rw->desa->nama }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </div>
            </div>

            {{-- Audit Trail --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Audit Trail</h3>
                </div>
                <div class="p-5">
                    <div class="relative pl-6 space-y-4">
                        {{-- Timeline line --}}
                        <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-gray-200"></div>

                        <div class="relative">
                            <div
                                class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-2 ring-white">
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-700">Dibuat</p>
                                <p class="text-sm text-gray-800">{{ $event->createdBy?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $event->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if ($event->status_data === 'VERIFIED' && $event->verifiedBy)
                            <div class="relative">
                                <div
                                    class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-blue-500 ring-2 ring-white">
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-700">Diverifikasi</p>
                                    <p class="text-sm text-gray-800">{{ $event->verifiedBy->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $event->verified_at?->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if ($event->updated_at != $event->created_at)
                            <div class="relative">
                                <div
                                    class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full bg-amber-500 ring-2 ring-white">
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-700">Terakhir diubah</p>
                                    <p class="text-xs text-gray-400">{{ $event->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Actions (untuk approver) --}}
    @include('data_peristiwa.partials.approval-actions', ['event' => $event])

    {{-- Void Modal --}}
    @can('void', $event)
        @include('data_peristiwa.partials.void-modal', ['event' => $event])
    @endcan
</x-app-layout>
