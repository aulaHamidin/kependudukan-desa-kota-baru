{{-- Data Peristiwa - Datang Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Data Peristiwa', 'url' => '#'],
            ['label' => 'Datang', 'url' => route('events.datang.index')],
            ['label' => 'Detail'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Detail Event Datang" subtitle="Informasi lengkap penduduk yang datang">
            <x-slot name="actions">
                <div class="flex gap-2">
                    @can('update', $event)
                        <x-button variant="warning" icon="edit" :href="route('events.datang.edit', $event)">
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
            default => 'from-indigo-500 to-indigo-700',
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
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    <h2 class="text-xl font-bold">{{ $event->penduduk?->nama_lengkap ?? '-' }}</h2>
                    @include('data_peristiwa.partials.status-badge', ['status' => $event->status_data])
                </div>
                <p class="text-white/70 text-sm">
                    Datang pada {{ $event->event_date?->format('d F Y') ?? '-' }} &middot;
                    {{ $event->eventDatang->alamat_asal ?? '-' }}
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
            {{-- Informasi Kedatangan --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Informasi Kedatangan</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jenis Kedatangan
                            </dt>
                            <dd class="mt-1">
                                @php
                                    $jenisMap = [
                                        'pendatang_baru' => 'Pendatang Baru',
                                        'pindah_masuk' => 'Pindah Masuk',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border
                                    {{ $event->eventDatang->jenis_kedatangan === 'pindah_masuk'
                                        ? 'bg-blue-50 text-blue-700 border-blue-200'
                                        : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                    {{ $jenisMap[$event->eventDatang->jenis_kedatangan] ?? ($event->eventDatang->jenis_kedatangan ?? '-') }}
                                </span>
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Datang
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventDatang->event_date?->format('d/m/Y') ?? ($event->event_date?->format('d/m/Y') ?? '-') }}
                            </dd>
                        </div>
                        <div class="md:col-span-2 bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Alamat Asal</dt>
                            <dd class="mt-1 text-sm text-gray-800 whitespace-pre-line">
                                {{ $event->eventDatang->alamat_asal ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Alasan Datang</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->eventDatang->alasan_datang ?? '-' }}</dd>
                        </div>
                        @if ($event->eventDatang?->keterangan_alasan)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Keterangan
                                </dt>
                                <dd class="mt-1 text-sm text-gray-800">{{ $event->eventDatang->keterangan_alasan }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Data Surat Pindah --}}
            @if ($event->eventDatang?->jenis_kedatangan === 'pindah_masuk')
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700">Data Surat Pindah</h3>
                    </div>
                    <div class="p-5">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nomor Surat
                                    Pindah</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-800 font-mono">
                                    {{ $event->eventDatang->no_surat_pindah ?? '-' }}</dd>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Surat
                                </dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-800">
                                    {{ $event->eventDatang->tanggal_surat_pindah?->format('d F Y') ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif

            {{-- Data Penduduk --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Penduduk</h3>
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
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Jenis Kelamin
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->jenis_kelamin === 'L' ? 'Laki-laki' : ($event->penduduk?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Tempat, Tanggal
                                Lahir</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->tempat_lahir ?? '-' }},
                                {{ \App\Support\Masking::date($event->penduduk?->tgl_lahir?->format('Y-m-d') ?? '') }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Agama</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->agama?->nama ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Status Perkawinan
                            </dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->status_perkawinan ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Ayah</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->nama_ayah ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Nama Ibu</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ \App\Support\Masking::text($event->penduduk?->nama_ibu ?? '') }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Pendidikan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->pendidikan?->nama ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Pekerjaan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">
                                {{ $event->penduduk?->pekerjaan?->nama ?? '-' }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">No. HP</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ \App\Support\Masking::phone($event->penduduk?->no_hp ?? '') }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <dt class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-800">{{ \App\Support\Masking::email($event->penduduk?->email ?? '') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Kartu Keluarga Tujuan --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Kartu Keluarga Tujuan</h3>
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
                            <p class="text-xs text-gray-500 mt-0.5">
                                RT {{ $event->rt->nomor_rt }} / RW {{ $event->rw->nomor_rw }}
                            </p>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">Tidak terdaftar dalam KK</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Wilayah Tujuan --}}
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
                    <h3 class="text-sm font-semibold text-gray-700">Wilayah Tujuan</h3>
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
        </div>
    </div>

    {{-- Approval Actions (untuk approver) --}}
    @include('data_peristiwa.partials.approval-actions', ['event' => $event])

    {{-- Void Modal --}}
    @can('void', $event)
        @include('data_peristiwa.partials.void-modal', ['event' => $event])
    @endcan
</x-app-layout>
