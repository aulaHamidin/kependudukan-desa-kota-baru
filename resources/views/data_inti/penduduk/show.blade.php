{{-- Data Inti - Penduduk Show --}}
<x-app-layout>
    <x-slot name="title">Detail Penduduk - {{ $penduduk->nama_lengkap }}</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Kependudukan', 'url' => '#'],
            ['label' => 'Penduduk', 'url' => route('penduduk.index')],
            ['label' => $penduduk->nama_lengkap],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Detail Penduduk" subtitle="{{ \App\Support\Masking::nik($penduduk->nik) }}">
            <x-slot name="actions">
                <x-button variant="secondary" icon="arrow-left" :href="route('penduduk.index')">
                    Kembali
                </x-button>
                @can('update', $penduduk)
                    <x-button variant="primary" icon="edit" :href="route('penduduk.edit', $penduduk)">
                        Edit Data
                    </x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    @php
        $statusCode = $penduduk->status_kependudukan_code;
        $statusGradient = match ($statusCode) {
            'AKTIF' => 'from-emerald-500 to-teal-700',
            'PINDAH' => 'from-amber-500 to-amber-700',
            'MENINGGAL' => 'from-gray-500 to-gray-700',
            default => 'from-blue-600 to-blue-800',
        };
        $genderIcon = $penduduk->jenis_kelamin === 'L';
    @endphp

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-br {{ $statusGradient }} rounded-xl p-5 sm:p-6 text-white shadow-lg mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div
                    class="w-14 h-14 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center text-2xl font-bold border border-white/20">
                    {{ strtoupper(substr($penduduk->nama_lengkap, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $penduduk->nama_lengkap }}</h2>
                    <p class="text-white/70 text-sm font-mono">NIK: {{ \App\Support\Masking::nik($penduduk->nik) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/15 text-white">
                            {{ $penduduk->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                        @if ($penduduk->tgl_lahir)
                            <span class="text-xs text-white/60">{{ $penduduk->tgl_lahir->age }} tahun</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-5">
                <div class="text-center sm:text-right">
                    <div class="text-xs text-white/60 uppercase tracking-wide mb-0.5">Status</div>
                    @php
                        $badgeType = match ($statusCode) {
                            'AKTIF' => 'aktif',
                            'PINDAH' => 'pindah',
                            'MENINGGAL' => 'meninggal',
                            default => 'pending',
                        };
                    @endphp
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/20 text-white">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $statusCode === 'AKTIF' ? 'bg-green-300' : ($statusCode === 'MENINGGAL' ? 'bg-gray-300' : 'bg-amber-300') }}"></span>
                        {{ $penduduk->statusKependudukan?->nama ?? $statusCode }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Completeness Indicator --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-lg flex items-center justify-center
                    {{ $dataCompleteness['percentage'] >= 80 ? 'bg-emerald-50' : ($dataCompleteness['percentage'] >= 50 ? 'bg-amber-50' : 'bg-red-50') }}">
                    <svg class="w-5 h-5 {{ $dataCompleteness['percentage'] >= 80 ? 'text-emerald-600' : ($dataCompleteness['percentage'] >= 50 ? 'text-amber-600' : 'text-red-600') }}"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Kelengkapan Data</h3>
                    <p class="text-xs text-gray-500">
                        {{ $dataCompleteness['filled'] }}/{{ $dataCompleteness['total'] }} field terisi</p>
                </div>
            </div>
            <div
                class="text-2xl font-bold {{ $dataCompleteness['percentage'] >= 80 ? 'text-emerald-600' : ($dataCompleteness['percentage'] >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                {{ $dataCompleteness['percentage'] }}%
            </div>
        </div>
        <div class="bg-gray-100 rounded-full h-2.5">
            <div class="h-2.5 rounded-full transition-all duration-500
                {{ $dataCompleteness['percentage'] >= 80 ? 'bg-emerald-500' : ($dataCompleteness['percentage'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                style="width: {{ $dataCompleteness['percentage'] }}%"></div>
        </div>
        @if ($dataCompleteness['percentage'] < 100)
            <p class="mt-2 text-xs text-gray-500">
                Belum terisi: <span
                    class="font-medium text-gray-700">{{ implode(', ', $dataCompleteness['missing']) }}</span>
            </p>
        @endif
    </div>

    {{-- Data Identitas --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Data Identitas</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">NIK</label>
                    <p class="mt-0.5 text-sm text-gray-900 font-mono font-semibold">
                        {{ \App\Support\Masking::nik($penduduk->nik) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nama Lengkap</label>
                    <p class="mt-0.5 text-sm text-gray-900 font-semibold">{{ $penduduk->nama_lengkap }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jenis Kelamin</label>
                    <p class="mt-0.5 text-sm text-gray-900">
                        {{ $penduduk->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tempat, Tanggal
                        Lahir</label>
                    <p class="mt-0.5 text-sm text-gray-900">
                        {{ $penduduk->tempat_lahir }}, {{ \App\Support\Masking::date($penduduk->tgl_lahir?->format('Y-m-d') ?? '') }}
                        <span class="text-xs text-gray-500">({{ $penduduk->tgl_lahir?->age }} tahun)</span>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Agama</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->agama?->nama ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status Perkawinan</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->status_perkawinan }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Kewarganegaraan</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->kewarganegaraan }}</p>
                </div>
                @if ($penduduk->no_paspor)
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">No. Paspor</label>
                        <p class="mt-0.5 text-sm text-gray-900 font-mono">{{ $penduduk->no_paspor }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Data Keluarga --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Data Keluarga</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nama Ayah</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->nama_ayah ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nama Ibu</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ \App\Support\Masking::text($penduduk->nama_ibu ?? '') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Pendidikan & Pekerjaan --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Pendidikan & Pekerjaan</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pendidikan</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->pendidikan?->nama ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pekerjaan</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->pekerjaan?->nama ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pendapatan</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->pendapatanRange?->label ?? '-' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Golongan Darah</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->golonganDarah?->nama ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Kontak & Alamat --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Kontak & Alamat</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">No. HP</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ \App\Support\Masking::phone($penduduk->no_hp ?? '') }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ \App\Support\Masking::email($penduduk->email ?? '') }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">RT / RW</label>
                    <p class="mt-0.5 text-sm text-gray-900">
                        RT {{ $penduduk->rt?->nomor_rt ?? '-' }} / RW {{ $penduduk->rt?->rw?->nomor_rw ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Kependudukan --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Status Kependudukan</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status</label>
                    <div class="mt-1">
                        @php
                            $badgeType = match ($statusCode) {
                                'AKTIF' => 'aktif',
                                'PINDAH' => 'pindah',
                                'MENINGGAL' => 'meninggal',
                                default => 'pending',
                            };
                        @endphp
                        <x-badge :type="$badgeType">{{ $penduduk->statusKependudukan?->nama ?? $statusCode }}</x-badge>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tanggal Status</label>
                    <p class="mt-0.5 text-sm text-gray-900">{{ $penduduk->tanggal_status?->format('d F Y') ?? '-' }}
                    </p>
                </div>
                @if ($penduduk->currentEvent)
                    <div class="md:col-span-2 bg-gray-50 rounded-lg px-4 py-3">
                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Event Terakhir</label>
                        <p class="mt-0.5 text-sm text-gray-900">
                            {{ $penduduk->currentEvent->eventType?->nama ?? 'Event' }}
                            <span class="text-xs text-gray-500">
                                ({{ $penduduk->currentEvent->created_at?->format('d F Y H:i') }})
                            </span>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat Kartu Keluarga --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Riwayat Kartu Keluarga</h3>
        </div>
        <div class="p-5">
            @forelse($penduduk->kkMembers as $member)
                <div class="flex items-start gap-4 py-4 border-b border-gray-100 last:border-0 first:pt-0 last:pb-0">
                    {{-- Icon --}}
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                        {{ $member->status === 'AKTIF' ? 'bg-emerald-50' : 'bg-gray-100' }}">
                        <svg class="w-5 h-5 {{ $member->status === 'AKTIF' ? 'text-emerald-600' : 'text-gray-400' }}"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('kartu-keluarga.show', $member->kartuKeluarga) }}"
                                class="font-medium text-primary-600 hover:text-primary-700 text-sm">
                                KK: {{ \App\Support\Masking::nik($member->kartuKeluarga?->no_kk ?? '') }}
                            </a>
                            <x-badge :type="$member->status === 'AKTIF' ? 'aktif' : 'danger'">
                                {{ $member->status }}
                            </x-badge>
                            @if ($member->is_kepala_keluarga)
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                    Kepala Keluarga
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-600 mt-1">
                            Hubungan: {{ $member->hubunganKeluarga?->nama ?? '-' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Masuk: {{ $member->tanggal_masuk?->format('d M Y') }}
                            @if ($member->tanggal_keluar)
                                &middot; Keluar: {{ $member->tanggal_keluar?->format('d M Y') }}
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada riwayat KK"
                    description="Penduduk ini belum terdaftar di kartu keluarga manapun." />
            @endforelse
        </div>
    </div>

    {{-- Riwayat Event --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Riwayat Event (10 Terakhir)</h3>
        </div>
        <div class="p-5">
            @forelse($penduduk->events as $event)
                <div class="flex items-start gap-4 py-3 border-b border-gray-100 last:border-0 first:pt-0 last:pb-0">
                    {{-- Timeline dot --}}
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                        {{ $event->status === 'APPROVED' ? 'bg-emerald-50' : 'bg-amber-50' }}">
                        <svg class="w-4 h-4 {{ $event->status === 'APPROVED' ? 'text-emerald-600' : 'text-amber-600' }}"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            @if ($event->status === 'APPROVED')
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @endif
                        </svg>
                    </div>
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-medium text-sm text-gray-900">
                                {{ $event->eventType?->nama ?? 'Event' }}
                            </p>
                            <x-badge :type="$event->status === 'APPROVED' ? 'aktif' : 'pending'">
                                {{ $event->status }}
                            </x-badge>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $event->event_date?->format('d F Y') }}
                        </p>
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada riwayat event"
                    description="Belum ada event yang tercatat untuk penduduk ini." />
            @endforelse
        </div>
    </div>
</x-app-layout>
