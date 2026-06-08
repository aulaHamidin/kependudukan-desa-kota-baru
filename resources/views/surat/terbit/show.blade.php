{{-- Surat - Terbit Show --}}
<x-app-layout>
    <x-slot name="title">Detail Surat: {{ $suratTerbit->nomor_surat ?? 'Terbit' }}</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Surat', 'url' => '#'],
            ['label' => 'Terbit', 'url' => route('surat.terbit.index')],
            ['label' => 'Detail'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail Surat Terbit" subtitle="Informasi rincian dari surat yang diterbitkan.">
            <x-slot name="actions">
                <x-button href="{{ route('surat.terbit.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    {{-- Hero Banner --}}
    @php
        $isAktif = $suratTerbit->status === 'AKTIF';
        $heroGradient = $isAktif
            ? 'from-amber-500 via-orange-500 to-yellow-600'
            : 'from-gray-500 via-gray-600 to-gray-700';
        $pemohonName = $suratTerbit->penduduk?->nama_lengkap ?? 'Tidak diketahui';
        $pemohonInitials = collect(explode(' ', $pemohonName))
            ->take(2)
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->join('');
    @endphp
    <div class="relative overflow-hidden rounded-xl bg-gradient-to-br {{ $heroGradient }} p-6 sm:p-8 mb-6 shadow-lg">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 400 200" fill="none">
                <circle cx="350" cy="30" r="80" fill="white" opacity="0.3" />
                <circle cx="380" cy="150" r="50" fill="white" opacity="0.2" />
                <circle cx="50" cy="170" r="60" fill="white" opacity="0.15" />
            </svg>
        </div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
            {{-- Doc Icon --}}
            <div
                class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white shadow-lg">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap mb-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-white truncate">
                        {{ $suratTerbit->nomor_surat ?? 'Belum ada nomor' }}
                    </h2>
                    @if ($isAktif)
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white/20 backdrop-blur text-xs font-bold text-white uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 mr-1.5"></span> Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg bg-red-500/30 backdrop-blur text-xs font-bold text-white uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-300 mr-1.5"></span> Dibatalkan
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5">
                    <span class="inline-flex items-center gap-1.5 text-sm text-white/80">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        {{ $suratTerbit->jenisSurat->nama ?? $suratTerbit->jenis_surat_kode }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm text-white/80">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        {{ $pemohonName }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm text-white/80">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ $suratTerbit->tanggal_terbit->format('d M Y') }}
                    </span>
                </div>
            </div>
            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 shrink-0">
                @can('download', $suratTerbit)
                    @if ($isAktif)
                        @if ($pdfAvailable && $suratTerbit->pdf_status === 'READY')
                            <x-button href="{{ route('surat.terbit.download', $suratTerbit) }}" variant="primary"
                                icon="download" target="_blank">
                                Unduh PDF
                            </x-button>
                        @else
                            <x-button variant="secondary" icon="clock" disabled>
                                PDF Diproses...
                            </x-button>
                        @endif
                    @endif
                @endcan
                @can('batalkan', $suratTerbit)
                    @if ($isAktif)
                        <x-button href="{{ route('surat.terbit.batalkan.form', $suratTerbit) }}" variant="danger"
                            icon="x-circle">
                            Batalkan
                        </x-button>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- PDF Processing Notice --}}
    @if (session('surat_generated') && $suratTerbit->pdf_status === 'PROCESSING')
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-800">Surat Sedang Diproses</h3>
                    <p class="mt-1 text-sm text-blue-700">
                        Sistem sedang membangun dokumen PDF (termasuk KOP, tanda tangan, dan cap).
                        Silakan refresh halaman ini beberapa detik lagi untuk mengunduh.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancelled Warning --}}
    @if (!$isAktif && $suratTerbit->alasan_batal)
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-5 shadow-sm">
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-800">Surat Dibatalkan</h3>
                    <p class="mt-1 text-sm text-red-700">
                        <strong>Alasan:</strong> {{ $suratTerbit->alasan_batal }}
                    </p>
                    @if ($suratTerbit->cancelled_at)
                        <p class="mt-1 text-xs text-red-500">
                            Dibatalkan pada {{ $suratTerbit->cancelled_at->format('d M Y, H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Status & Info Utama --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Status Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-lg {{ $isAktif ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Status Dokumen</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status Surat</span>
                        @if ($isAktif)
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-red-50 text-red-700 text-xs font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span> Dibatalkan
                            </span>
                        @endif
                    </div>
                    <div class="border-t border-gray-100"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status PDF</span>
                        @if ($suratTerbit->pdf_status === 'READY')
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold">
                                Siap Unduh
                            </span>
                        @elseif ($suratTerbit->pdf_status === 'PROCESSING')
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold">
                                <svg class="w-3 h-3 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Memproses
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 text-gray-500 text-xs font-bold">
                                Menunggu
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Main Info Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Informasi Surat</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Nomor Surat</p>
                        <p class="text-sm font-bold text-gray-800 font-mono">
                            {{ $suratTerbit->nomor_surat ?? 'Belum ada nomor' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Jenis Surat</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $suratTerbit->jenisSurat->nama ?? $suratTerbit->jenis_surat_kode }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Diterbitkan
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $suratTerbit->tanggal_terbit->format('d M Y') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Berlaku s/d
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $suratTerbit->tanggal_kadaluarsa ? $suratTerbit->tanggal_kadaluarsa->format('d M Y') : 'Selamanya' }}
                            </p>
                        </div>
                    </div>
                    @if ($suratTerbit->tanggal_kadaluarsa)
                        @php
                            $daysLeft = now()->diffInDays($suratTerbit->tanggal_kadaluarsa, false);
                            $isExpired = $daysLeft < 0;
                            $isExpiring = !$isExpired && $daysLeft <= 30;
                        @endphp
                        @if ($isExpired)
                            <div
                                class="rounded-lg bg-red-50 border border-red-100 px-4 py-2.5 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                                <p class="text-xs font-semibold text-red-700">Surat sudah kedaluwarsa
                                    {{ abs((int) $daysLeft) }} hari yang lalu</p>
                            </div>
                        @elseif ($isExpiring)
                            <div
                                class="rounded-lg bg-amber-50 border border-amber-100 px-4 py-2.5 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs font-semibold text-amber-700">Berlaku {{ (int) $daysLeft }} hari lagi
                                </p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Pemohon & Keperluan --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Data Pemohon --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Data Pemohon</h3>
                </div>

                @if ($suratTerbit->penduduk)
                    <div class="p-5">
                        {{-- Pemohon Header --}}
                        <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                            <div
                                class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm shadow-sm">
                                {{ $pemohonInitials }}
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-800">
                                    {{ $suratTerbit->penduduk->nama_lengkap }}</p>
                                <p class="text-xs text-gray-500 font-mono">NIK: {{ \App\Support\Masking::nik($suratTerbit->penduduk->nik) }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg px-4 py-3">
                                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Tempat,
                                    Tanggal Lahir</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $suratTerbit->penduduk->tempat_lahir }},
                                    {{ \App\Support\Masking::date($suratTerbit->penduduk->tgl_lahir?->format('Y-m-d') ?? '') }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg px-4 py-3">
                                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Wilayah
                                </p>
                                <p class="text-sm font-semibold text-gray-800">
                                    RT {{ $suratTerbit->penduduk->rt?->nomor_rt ?? '-' }} / RW
                                    {{ $suratTerbit->penduduk->rt?->rw?->nomor_rw ?? '-' }}
                                </p>
                            </div>
                            <div class="sm:col-span-2 bg-gray-50 rounded-lg px-4 py-3">
                                <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Desa</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $suratTerbit->penduduk->rt?->rw?->desa?->nama ?? 'Sistem Pusat' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-400">Data penduduk tidak ditemukan</p>
                        <p class="text-xs text-gray-300 mt-1">Data mungkin telah dihapus dari sistem.</p>
                    </div>
                @endif
            </div>

            {{-- Keperluan & Keterangan --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Deskripsi &amp; Keperluan</h3>
                </div>
                <div class="p-5 space-y-5">
                    <div>
                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-2">Tujuan
                            Penggunaan Surat</p>
                        <div class="bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $suratTerbit->keperluan ?: '-' }}</p>
                        </div>
                    </div>
                    @if ($suratTerbit->keterangan_tambahan)
                        <div>
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-2">Keterangan
                                Tambahan</p>
                            <div class="bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">
                                <p class="text-sm text-gray-700 leading-relaxed">
                                    {{ $suratTerbit->keterangan_tambahan }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
