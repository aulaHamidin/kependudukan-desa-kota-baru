{{-- Data Peristiwa - Kematian Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Kematian', 'url' => route('events.kematian.index')],
            ['label' => 'Detail'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Detail Event Kematian" subtitle="Informasi lengkap kematian penduduk">
            <x-slot name="actions">
                <div class="flex gap-2">
                    @can('update', $event)
                        <x-button variant="warning" icon="edit" :href="route('events.kematian.edit', $event)">
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
        $kematian = $event->eventKematian;
        $statusGradient = match ($event->status_data) {
            'VERIFIED' => 'from-emerald-500 to-emerald-700',
            'DRAFT' => 'from-amber-500 to-amber-700',
            'VOID' => 'from-red-500 to-red-700',
            default => 'from-gray-600 to-gray-800',
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
                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <h2 class="text-xl font-bold">{{ $event->penduduk?->nama_lengkap ?? '-' }}</h2>
                    @include('data_peristiwa.partials.status-badge', ['status' => $event->status_data])
                </div>
                <p class="text-white/70 text-sm">
                    Meninggal pada {{ $event->event_date?->format('d F Y') ?? '-' }} &middot;
                    {{ $kematian?->tempat_meninggal ?? '-' }}
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

            {{-- Data Almarhum --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Almarhum/ah</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">NIK</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                <span class="data-nik">{{ \App\Support\Masking::nik($event->penduduk?->nik ?? '') }}</span>
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @if ($event->penduduk)
                                    <a href="{{ route('penduduk.show', $event->penduduk) }}"
                                        class="text-blue-600 hover:underline">{{ $event->penduduk->nama_lengkap }}</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="md:col-span-2 bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Kartu Keluarga
                            </dt>
                            <dd class="mt-1 text-sm text-gray-800">
                                @if ($event->kartuKeluarga)
                                    <a href="{{ route('kartu-keluarga.show', $event->kartuKeluarga) }}"
                                        class="text-blue-600 hover:underline font-mono font-semibold">{{ \App\Support\Masking::nik($event->kartuKeluarga->no_kk) }}</a>
                                    @if ($event->kartuKeluarga->kepalaKeluarga?->penduduk)
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Kepala KK:
                                            {{ $event->kartuKeluarga->kepalaKeluarga->penduduk->nama_lengkap }}
                                        </p>
                                    @endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Informasi Kematian --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Informasi Kematian</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Meninggal
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->event_date?->format('d F Y') ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jam Meninggal</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ $kematian?->jam_meninggal ?? '-' }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tempat Meninggal
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kematian?->tempat_meninggal ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Sebab Kematian
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kematian?->sebab_kematian ?? '-' }}</dd>
                        </div>
                        @if ($kematian?->penyakit)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Penyakit</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ $kematian->penyakit }}</dd>
                            </div>
                        @endif
                        @if ($kematian?->keterangan_kematian)
                            <div class="md:col-span-2 bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Keterangan
                                    Kematian</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ $kematian->keterangan_kematian }}</dd>
                            </div>
                        @endif
                        @if ($event->keterangan)
                            <div class="md:col-span-2 bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Keterangan
                                    Event</dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ $event->keterangan }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Data Pelapor --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Pelapor</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Pelapor</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                @if ($kematian?->pelapor)
                                    <a href="{{ route('penduduk.show', $kematian->pelapor) }}"
                                        class="text-blue-600 hover:underline">{{ $kematian->pelapor->nama_lengkap }}</a>
                                    <span class="text-xs text-gray-500">(Penduduk)</span>
                                @elseif ($kematian?->nama_pelapor)
                                    {{ $kematian->nama_pelapor }}
                                    <span class="text-xs text-gray-500">(Non-penduduk)</span>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Hubungan dengan
                                Almarhum</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $kematian?->hubunganPelapor?->nama ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Pengganti Kepala Keluarga --}}
            @if ($kematian?->was_kepala)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">Pengganti Kepala Keluarga</h3>
                    </div>
                    <div class="p-5">
                        @if ($kematian->pengganti)
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama
                                        Pengganti</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-800">
                                        <a href="{{ route('penduduk.show', $kematian->pengganti) }}"
                                            class="text-blue-600 hover:underline">{{ $kematian->pengganti->nama_lengkap }}</a>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">NIK</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-800">
                                        <span class="data-nik">{{ \App\Support\Masking::nik($kematian->pengganti->nik) }}</span>
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <p class="text-sm text-amber-700">
                                    Almarhum adalah kepala keluarga namun tidak ada anggota KK lain yang ditunjuk
                                    sebagai pengganti. KK telah dinonaktifkan.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Wilayah --}}
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
                            {{ $event->rt?->rw?->desa?->nama ?? '-' }}</dd>
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
        </div>
    </div>

    {{-- Approval Actions (untuk approver) --}}
    @include('data_peristiwa.partials.approval-actions', ['event' => $event])

    {{-- Void Modal --}}
    @can('void', $event)
        @include('data_peristiwa.partials.void-modal', ['event' => $event])
    @endcan
</x-app-layout>
