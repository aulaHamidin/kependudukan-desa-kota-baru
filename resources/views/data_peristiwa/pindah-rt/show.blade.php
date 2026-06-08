{{-- Pindah RT - Form Konfirmasi --}}
<x-app-layout>
    <x-slot name="title">Pindah RT &mdash; {{ \App\Support\Masking::nik($kk->no_kk) }}</x-slot>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Administrasi', 'url' => '#'],
            ['label' => 'Pindah RT', 'url' => route('pindah-rt.index')],
            ['label' => \App\Support\Masking::nik($kk->no_kk)],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Pindah RT" subtitle="Pindahkan KK {{ \App\Support\Masking::nik($kk->no_kk) }} ke RT lain.">
            <x-slot name="actions">
                <x-button variant="secondary" icon="arrow-left" :href="route('pindah-rt.index')">
                    Kembali
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    {{-- Hero Banner - KK Info --}}
    @php
        $kepala = $kk->kkMembers->firstWhere('is_kepala_keluarga', true);
        $kepalaName = $kepala?->penduduk?->nama_lengkap ?? '-';
        $initials = collect(explode(' ', $kepalaName))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->join('');
    @endphp
    <div
        class="relative overflow-hidden rounded-xl bg-gradient-to-br from-cyan-600 via-teal-600 to-emerald-700 p-6 sm:p-8 mb-6 shadow-lg">
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 400 200" fill="none">
                <circle cx="350" cy="30" r="80" fill="white" opacity="0.3" />
                <circle cx="380" cy="150" r="50" fill="white" opacity="0.2" />
                <circle cx="50" cy="170" r="60" fill="white" opacity="0.15" />
            </svg>
        </div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-5">
            {{-- Avatar --}}
            <div
                class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white font-bold text-xl shadow-lg">
                {{ $initials }}
            </div>
            <div class="flex-1">
                <h2 class="text-xl sm:text-2xl font-bold text-white">{{ $kepalaName }}</h2>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5">
                    <span class="inline-flex items-center gap-1.5 text-sm text-teal-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                        <span class="font-mono">{{ \App\Support\Masking::nik($kk->no_kk) }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm text-teal-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        RT {{ $kk->rt?->nomor_rt ?? '-' }} / RW {{ $kk->rt?->rw?->nomor_rw ?? '-' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-sm text-teal-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        {{ $anggotaCount }} Anggota
                    </span>
                </div>
            </div>
            {{-- Transfer indicator --}}
            <div class="hidden sm:flex items-center gap-3">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <p class="text-[10px] text-teal-200 font-medium uppercase tracking-wider">Pindah RT</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Warning: Ada event DRAFT --}}
    @if ($draftEvents->isNotEmpty())
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-800">Tidak Dapat Memindahkan RT</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Terdapat {{ $draftEvents->count() }} event berstatus <strong>DRAFT</strong> yang terkait dengan
                        KK atau anggotanya.
                        Selesaikan atau batalkan event tersebut terlebih dahulu.
                    </p>
                    <div class="mt-3 space-y-2">
                        @foreach ($draftEvents as $draft)
                            @php
                                $draftColors = [
                                    'KELAHIRAN' => [
                                        'bg' => 'bg-emerald-50',
                                        'text' => 'text-emerald-700',
                                        'border' => 'border-emerald-200',
                                    ],
                                    'KEMATIAN' => [
                                        'bg' => 'bg-gray-100',
                                        'text' => 'text-gray-700',
                                        'border' => 'border-gray-200',
                                    ],
                                    'PINDAH' => [
                                        'bg' => 'bg-amber-50',
                                        'text' => 'text-amber-700',
                                        'border' => 'border-amber-200',
                                    ],
                                    'DATANG' => [
                                        'bg' => 'bg-blue-50',
                                        'text' => 'text-blue-700',
                                        'border' => 'border-blue-200',
                                    ],
                                ];
                                $dc = $draftColors[$draft->event_type_code] ?? [
                                    'bg' => 'bg-gray-50',
                                    'text' => 'text-gray-700',
                                    'border' => 'border-gray-200',
                                ];
                            @endphp
                            <div
                                class="flex items-center gap-3 {{ $dc['bg'] }} border {{ $dc['border'] }} rounded-lg px-3 py-2">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $dc['text'] }} {{ $dc['bg'] }}">
                                    {{ $draft->eventType?->nama ?? $draft->event_type_code }}
                                </span>
                                <span class="text-xs text-gray-600">
                                    {{ $draft->penduduk?->nama_lengkap ?? '-' }}
                                </span>
                                <span class="text-xs text-gray-400 ml-auto">
                                    {{ $draft->event_date?->format('d M Y') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri: Info KK + Anggota --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info KK Saat Ini --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Informasi Kartu Keluarga</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">No. KK</p>
                            <p class="text-sm font-semibold text-gray-800 font-mono">
                                {{ \App\Support\Masking::nik($kk->no_kk) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Alamat</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $kk->alamat }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">RT / RW Saat
                                Ini</p>
                            <p class="text-sm font-semibold text-gray-800">
                                RT {{ $kk->rt?->nomor_rt ?? '-' }} / RW {{ $kk->rt?->rw?->nomor_rw ?? '-' }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-4 py-3">
                            <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider mb-1">Desa</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $kk->rt?->rw?->desa?->nama ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Anggota yang Terdampak --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700">Anggota yang Terdampak</h3>
                            <p class="text-xs text-gray-400">Seluruh anggota aktif akan ikut berpindah RT</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold">
                        {{ $anggotaCount }} Orang
                    </span>
                </div>
                <div class="p-4 sm:p-5 space-y-2">
                    @forelse ($kk->kkMembers as $member)
                        @php
                            $memberName = $member->penduduk?->nama_lengkap ?? '-';
                            $memberInitials = collect(explode(' ', $memberName))
                                ->take(2)
                                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                ->join('');
                            $isKepala = $member->is_kepala_keluarga;
                            $gender = $member->penduduk?->jenis_kelamin;
                            $age = $member->penduduk?->tgl_lahir ? $member->penduduk->tgl_lahir->age : null;
                        @endphp
                        <div
                            class="flex items-center gap-4 p-3 rounded-xl {{ $isKepala ? 'bg-teal-50 border border-teal-200' : 'bg-gray-50 border border-gray-100' }} hover:shadow-sm transition-all">
                            {{-- Avatar --}}
                            <div
                                class="w-10 h-10 rounded-lg {{ $isKepala ? 'bg-gradient-to-br from-teal-500 to-emerald-600 text-white' : ($gender === 'L' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600') }} flex items-center justify-center shrink-0 font-bold text-xs shadow-sm">
                                {{ $memberInitials }}
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-semibold text-gray-800">{{ $memberName }}</p>
                                    @if ($isKepala)
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-700 uppercase tracking-wide">
                                            Kepala KK
                                        </span>
                                    @endif
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $gender === 'L' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' }}">
                                        {{ $gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                                <div
                                    class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5 text-xs text-gray-500">
                                    <span
                                        class="font-mono text-[11px]">{{ \App\Support\Masking::nik($member->penduduk?->nik ?? '') }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span>{{ $member->hubunganKeluarga?->nama ?? '-' }}</span>
                                    @if ($age !== null)
                                        <span class="text-gray-300">|</span>
                                        <span>{{ $age }} tahun</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Arrow indicator --}}
                            <div class="hidden sm:flex items-center text-gray-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div
                                class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-400">Tidak ada anggota aktif</p>
                            <p class="text-xs text-gray-300 mt-1">KK ini tidak memiliki anggota aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Pindah RT --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Form Pindah RT</h3>
                </div>

                <div class="p-5">
                    @if ($draftEvents->isNotEmpty())
                        {{-- Form dinonaktifkan karena ada DRAFT --}}
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-6 text-center">
                            <div
                                class="w-12 h-12 rounded-xl bg-amber-100 text-amber-500 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-600">Form Terkunci</p>
                            <p class="text-xs text-gray-400 mt-1">Selesaikan event DRAFT terlebih dahulu untuk
                                mengaktifkan form ini.</p>
                        </div>
                    @else
                        {{-- Transfer Visual --}}
                        <div
                            class="mb-5 rounded-xl bg-gradient-to-r from-teal-50 to-emerald-50 border border-teal-100 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-center flex-1">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center mx-auto mb-1.5">
                                        <span class="text-xs font-bold">{{ $kk->rt?->nomor_rt ?? '?' }}</span>
                                    </div>
                                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">RT Asal
                                    </p>
                                    <p class="text-xs text-gray-600 font-semibold">RW
                                        {{ $kk->rt?->rw?->nomor_rw ?? '-' }}</p>
                                </div>
                                <div class="shrink-0">
                                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </div>
                                <div class="text-center flex-1">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-1.5">
                                        <span class="text-xs font-bold">?</span>
                                    </div>
                                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">RT Tujuan
                                    </p>
                                    <p class="text-xs text-gray-600 font-semibold">Pilih di bawah</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('pindah-rt.store', $kk) }}" class="space-y-5"
                            x-data="swalConfirm({
                                title: 'Konfirmasi Pindah RT?',
                                text: 'Yakin ingin memindahkan KK ini ke RT yang dipilih? Seluruh {{ $anggotaCount }} anggota aktif akan ikut berpindah.',
                                confirmText: 'Ya, Pindahkan',
                                cancelText: 'Batal'
                            })" @submit="submit">
                            @csrf

                            {{-- RT Tujuan --}}
                            <div>
                                <x-form-select name="rt_id_tujuan" label="RT Tujuan" required :options="$rtOptions"
                                    :value="old('rt_id_tujuan')" placeholder="Pilih RT tujuan..." />
                            </div>

                            {{-- Keterangan --}}
                            <div>
                                <x-form-textarea name="keterangan" label="Keterangan"
                                    placeholder="Contoh: Pemekaran RT 03 menjadi RT 04" rows="3"
                                    :value="old('keterangan')" />
                                <p class="mt-1 text-xs text-gray-400">
                                    Opsional. Dicatat di audit log sebagai alasan perpindahan.
                                </p>
                            </div>

                            {{-- Info --}}
                            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 flex gap-3">
                                <div
                                    class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                    </svg>
                                </div>
                                <p class="text-xs text-blue-700">
                                    Seluruh <strong>{{ $anggotaCount }} anggota aktif</strong> akan ikut berpindah ke
                                    RT yang dipilih.
                                    Tindakan ini akan dicatat di audit log.
                                </p>
                            </div>

                            <div class="flex flex-col gap-2 pt-3 border-t border-gray-100">
                                <x-button type="submit" variant="village" icon="check">
                                    Konfirmasi Pindah RT
                                </x-button>
                                <x-button variant="secondary" :href="route('pindah-rt.index')">
                                    Batal
                                </x-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
