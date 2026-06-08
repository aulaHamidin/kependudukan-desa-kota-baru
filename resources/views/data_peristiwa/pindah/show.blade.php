{{-- Data Peristiwa - Pindah Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Pindah', 'url' => route('events.pindah.index')],
            ['label' => 'Detail'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Detail Event Pindah" subtitle="Informasi lengkap peristiwa pindah domisili">
            <x-slot name="actions">
                <div class="flex gap-2">
                    @can('update', $event)
                        <x-button variant="warning" icon="edit" :href="route('events.pindah.edit', $event)">
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
        $statusGradient = match ($event->status_data) {
            'VERIFIED' => 'from-emerald-500 to-emerald-700',
            'DRAFT' => 'from-amber-500 to-amber-700',
            'VOID' => 'from-red-500 to-red-700',
            default => 'from-blue-500 to-blue-700',
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
                            d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3" />
                    </svg>
                    <h2 class="text-xl font-bold">{{ $event->penduduk?->nama_lengkap ?? '-' }}</h2>
                    @include('data_peristiwa.partials.status-badge', ['status' => $event->status_data])
                </div>
                <p class="text-white/70 text-sm">
                    Pindah pada {{ $event->event_date->format('d F Y') }} &middot;
                    {{ $event->eventPindah->alamat_tujuan ?? '-' }}
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Data Penduduk --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Penduduk yang Pindah</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @if ($event->penduduk)
                                    <a href="{{ route('penduduk.show', $event->penduduk) }}"
                                        class="text-blue-600 hover:underline">{{ $event->penduduk->nama_lengkap }}</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">NIK</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                <span class="data-nik">{{ \App\Support\Masking::nik($event->penduduk->nik ?? '') }}</span>
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jenis Kelamin</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->jenis_kelamin === 'L' ? 'Laki-laki' : ($event->penduduk?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Pindah
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->event_date->format('d F Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Tujuan Pindah --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Tujuan Pindah</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Alamat Tujuan</dt>
                        <dd class="mt-1 text-sm text-gray-800 whitespace-pre-line">
                            {{ $event->eventPindah->alamat_tujuan ?? '-' }}</dd>
                    </div>

                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">RT</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->rt_tujuan ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">RW</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->rw_tujuan ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Desa/Kelurahan
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->desa_tujuan ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Kode Pos</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->kode_pos_tujuan ?? '-' }}</dd>
                        </div>
                    </dl>

                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Kecamatan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->kecamatan_tujuan ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Kabupaten/Kota
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->kabupaten_tujuan ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Provinsi</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->provinsi_tujuan ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Alasan Pindah --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Alasan Pindah</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Alasan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @php
                                    $alasanMap = [
                                        'PEKERJAAN' => 'Pekerjaan',
                                        'PENDIDIKAN' => 'Pendidikan',
                                        'KEAMANAN' => 'Keamanan',
                                        'KESEHATAN' => 'Kesehatan',
                                        'PERKAWINAN' => 'Perkawinan',
                                        'LAINNYA' => 'Lainnya',
                                    ];
                                @endphp
                                {{ $alasanMap[$event->eventPindah->alasan_pindah] ?? ($event->eventPindah->alasan_pindah ?? '-') }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jenis Kepindahan
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventPindah->jenis_kepindahan ?? '-' }}</dd>
                        </div>
                    </dl>

                    @if ($event->eventPindah?->keterangan_alasan)
                        <div class="mt-4 bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Keterangan Alasan
                            </dt>
                            <dd class="mt-1 text-sm text-gray-800 whitespace-pre-line">
                                {{ $event->eventPindah->keterangan_alasan }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pengganti Kepala Keluarga --}}
            @if ($event->eventPindah?->penggantiKepala)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">Pengganti Kepala Keluarga</h3>
                    </div>
                    <div class="p-5">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Pengganti
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                <a href="{{ route('penduduk.show', $event->eventPindah->penggantiKepala) }}"
                                    class="text-blue-600 hover:underline">{{ $event->eventPindah->penggantiKepala->nama_lengkap }}</a>
                                <span class="text-gray-500 ml-2">(<span
                                        class="data-nik">{{ \App\Support\Masking::nik($event->eventPindah->penggantiKepala->nik) }}</span>)</span>
                            </dd>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Kartu Keluarga --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Kartu Keluarga</h3>
                </div>
                <div class="p-5">
                    @if ($event->kartuKeluarga)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <a href="{{ route('kartu-keluarga.show', $event->kartuKeluarga) }}"
                                class="text-blue-600 hover:underline font-semibold text-sm">{{ \App\Support\Masking::nik($event->kartuKeluarga->no_kk) }}</a>
                            <p class="text-xs text-gray-500 mt-1">
                                @if ($event->kartuKeluarga->kepalaKeluarga && $event->kartuKeluarga->kepalaKeluarga->penduduk)
                                    Kepala Keluarga:
                                    {{ $event->kartuKeluarga->kepalaKeluarga->penduduk->nama_lengkap }}
                                @else
                                    Kepala Keluarga: -
                                @endif
                            </p>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">-</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Wilayah Asal --}}
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
                    <h3 class="text-sm font-semibold text-gray-700">Wilayah Asal</h3>
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

            {{-- Keterangan --}}
            @if ($event->keterangan)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">Keterangan</h3>
                    </div>
                    <div class="p-5">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $event->keterangan }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Approval Actions (untuk approver) --}}
    @include('data_peristiwa.partials.approval-actions', ['event' => $event])

    {{-- Void Modal --}}
    @can('void', $event)
        @include('data_peristiwa.partials.void-modal', ['event' => $event])
    @endcan
</x-app-layout>
