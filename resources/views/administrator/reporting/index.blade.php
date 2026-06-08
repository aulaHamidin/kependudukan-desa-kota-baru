{{-- Administrator Reporting --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Administrator', 'url' => '#'], ['label' => 'View & Reporting']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="View & Reporting" subtitle="Laporan penduduk, KK, konsistensi data, dan event" />
    </x-slot>

    <x-alert />

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $statConfigs = [
                'penduduk' => ['label' => 'Penduduk Aktif', 'icon' => 'users', 'color' => 'primary'],
                'kk' => ['label' => 'Kartu Keluarga', 'icon' => 'home', 'color' => 'indigo'],
                'inconsistency' => ['label' => 'Data Inconsistency', 'icon' => 'alert', 'color' => 'amber'],
                'events' => ['label' => 'Total Event', 'icon' => 'calendar', 'color' => 'emerald'],
            ];
        @endphp
        @foreach ($statConfigs as $key => $config)
            <a href="{{ route('administrator.reporting.index', ['type' => $key]) }}"
                class="group bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-5 {{ $type === $key ? 'ring-2 ring-' . $config['color'] . '-500 ring-offset-2' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        @if ($config['icon'] === 'users')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        @elseif($config['icon'] === 'home')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        @elseif($config['icon'] === 'alert')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        @endif
                    </div>
                    @if ($type === $key)
                        <span
                            class="text-xs font-bold bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-600 px-2 py-0.5 rounded-full">Aktif</span>
                    @endif
                </div>
                <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($data->total()) }}</p>
                <p class="text-xs text-gray-500">{{ $config['label'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Main Content Card --}}
    <x-card>
        {{-- Filter Section --}}
        <div class="bg-gray-50/60 rounded-lg border border-gray-100 p-5 mb-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Filter Data</h3>
            </div>

            <form method="GET" action="{{ route('administrator.reporting.index') }}"
                class="grid grid-cols-1 md:grid-cols-5 gap-3"
                x-data="{
                    hasFilters: {{ count(array_filter($filters ?? [])) > 0 ? 'true' : 'false' }},
                    checkFilters() {
                        const form = this.$root;
                        const inputs = form.querySelectorAll('input[name]:not([type=hidden]), select[name]');
                        this.hasFilters = Array.from(inputs).some(el => el.value !== '');
                    },
                    resetFilters() {
                        const form = this.$root;
                        const inputs = form.querySelectorAll('input[name]:not([type=hidden]), select[name]');
                        inputs.forEach(el => el.value = '');
                        this.hasFilters = false;
                    }
                }"
                x-on:input="checkFilters()" x-on:change="checkFilters()">
                <input type="hidden" name="type" value="{{ $type }}">
                <x-form-input type="search" name="search" label="Cari" placeholder="Nama / NIK" :value="$filters['search'] ?? ''"
                    class="md:col-span-2" />

                @if (in_array($type, ['penduduk', 'kk', 'events'], true))
                    <x-form-select name="rt_id" label="RT" :value="$filters['rt_id'] ?? ''">
                        <option value="">Semua RT</option>
                        @foreach ($rts as $id => $label)
                            <option value="{{ $id }}"
                                {{ ($filters['rt_id'] ?? null) == $id ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-form-select>
                @endif

                @if ($type === 'events')
                    <x-form-select name="event_type" label="Jenis Event" :value="$filters['event_type'] ?? ''">
                        <option value="">Semua</option>
                        @foreach ($eventTypes as $et)
                            <option value="{{ $et->kode }}"
                                {{ ($filters['event_type'] ?? null) === $et->kode ? 'selected' : '' }}>
                                {{ $et->nama }}
                            </option>
                        @endforeach
                    </x-form-select>

                    <x-form-select name="status_data" label="Status" :value="$filters['status_data'] ?? ''">
                        <option value="">Semua</option>
                        <option value="DRAFT" {{ ($filters['status_data'] ?? '') === 'DRAFT' ? 'selected' : '' }}>Draf
                        </option>
                        <option value="VERIFIED"
                            {{ ($filters['status_data'] ?? '') === 'VERIFIED' ? 'selected' : '' }}>
                            Terverifikasi</option>
                        <option value="VOID" {{ ($filters['status_data'] ?? '') === 'VOID' ? 'selected' : '' }}>Void
                        </option>
                    </x-form-select>

                    <x-form-input type="date" name="start_date" label="Dari Tanggal" :value="$filters['start_date'] ?? ''" />
                    <x-form-input type="date" name="end_date" label="Sampai Tanggal" :value="$filters['end_date'] ?? ''" />
                @endif

                <div class="flex items-end gap-2 md:col-span-5">
                    <x-button type="submit" variant="primary" class="min-w-[160px]">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Terapkan Filter
                    </x-button>
                    <x-button type="button" variant="secondary" class="min-w-[100px]"
                        x-show="hasFilters" x-cloak
                        x-on:click="resetFilters()">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </x-button>
                </div>
            </form>
        </div>

        {{-- Active Filters & Actions Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-4 border-b border-gray-100">
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $chips = [];
                    foreach (
                        [
                            'search' => 'Cari',
                            'rt_id' => 'RT',
                            'event_type' => 'Event',
                            'status_data' => 'Status',
                            'start_date' => 'Dari',
                            'end_date' => 'Sampai',
                        ]
                        as $key => $label
                    ) {
                        if (!empty($filters[$key])) {
                            $value = $filters[$key];
                            if ($key === 'rt_id' && isset($rts[$value])) {
                                $value = $rts[$value];
                            }
                            if ($key === 'event_type' && ($et = $eventTypes->firstWhere('kode', $value))) {
                                $value = $et->nama;
                            }
                            $chips[] = ['label' => $label, 'value' => $value];
                        }
                    }
                @endphp
                @if (count($chips))
                    <span class="text-xs font-semibold text-gray-500">Filter Aktif:</span>
                    @foreach ($chips as $chip)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $chip['label'] }}: <span class="font-bold">{{ $chip['value'] }}</span>
                        </span>
                    @endforeach
                    <a href="{{ route('administrator.reporting.index', ['type' => $type]) }}"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hapus Semua
                    </a>
                @else
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        <span class="font-semibold">{{ number_format($data->total()) }}</span> data ditemukan
                        @if ($data->hasPages())
                            <span class="text-gray-400">•</span>
                            <span>Halaman {{ $data->currentPage() }} dari {{ $data->lastPage() }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-gray-500">Ekspor:</span>
                <x-button variant="secondary" size="sm" :href="route(
                    'administrator.reporting.export',
                    array_merge(request()->query(), ['type' => $type, 'format' => 'pdf']),
                )">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    PDF
                </x-button>
                <x-button variant="secondary" size="sm" :href="route(
                    'administrator.reporting.export',
                    array_merge(request()->query(), ['type' => $type, 'format' => 'xlsx']),
                )">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" />
                    </svg>
                    Excel
                </x-button>
            </div>
        </div>

        {{-- Data Table --}}
        @php
            $colspan = match ($type) {
                'kk' => 6,
                'inconsistency' => 5,
                'events' => 7,
                default => 8,
            };
        @endphp
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        @if ($type === 'penduduk')
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    NIK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Nama</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    JK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    RT/RW</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Agama</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Pendidikan</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Pekerjaan</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        @elseif ($type === 'kk')
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    No KK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Kepala</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    NIK Kepala</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Anggota</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    RT/RW</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Alamat</th>
                            </tr>
                        @elseif ($type === 'inconsistency')
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Issue</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    NIK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Nama</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    KK ID</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Deskripsi</th>
                            </tr>
                        @elseif ($type === 'events')
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Jenis</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Tanggal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    NIK</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Nama</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    RT/RW</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Keterangan</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($data as $row)
                            @if ($type === 'penduduk')
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ \App\Support\Masking::nik($row->nik) }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $row->nama_lengkap }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $row->jenis_kelamin === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                            {{ $row->jenis_kelamin }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">RT {{ $row->nomor_rt }} / RW
                                        {{ $row->nomor_rw }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->agama }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->pendidikan }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->pekerjaan }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            {{ $row->status_kependudukan }}
                                        </span>
                                    </td>
                                </tr>
                            @elseif ($type === 'kk')
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ \App\Support\Masking::nik($row->no_kk) }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $row->nama_kepala ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                        {{ \App\Support\Masking::nik($row->nik_kepala ?? '') }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                            {{ $row->jumlah_anggota }} orang
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">RT {{ $row->nomor_rt }} / RW
                                        {{ $row->nomor_rw }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->alamat }}</td>
                                </tr>
                            @elseif ($type === 'inconsistency')
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700">
                                            {{ $row->issue_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ \App\Support\Masking::nik($row->nik) }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $row->nama_lengkap }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->kartu_keluarga_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->description }}</td>
                                </tr>
                            @elseif ($type === 'events')
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3">
                                        @php
                                            $eventColors = [
                                                'KELAHIRAN' => 'bg-emerald-100 text-emerald-700',
                                                'KEMATIAN' => 'bg-gray-100 text-gray-700',
                                                'PINDAH' => 'bg-amber-100 text-amber-700',
                                                'DATANG' => 'bg-blue-100 text-blue-700',
                                            ];
                                            $eventColor =
                                                $eventColors[$row->event_type_code] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $eventColor }}">
                                            {{ $row->event_type_code }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $row->event_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColor =
                                                [
                                                    'DRAFT' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    'VERIFIED' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'VOID' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                ][$row->status_data] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusColor }}">
                                            {{ $row->status_data }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                        {{ \App\Support\Masking::nik($row->penduduk?->nik ?? '') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $row->penduduk?->nama_lengkap ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        RT {{ $row->rt?->nomor_rt ?? '-' }} / RW {{ $row->rt?->rw?->nomor_rw ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->keterangan ?? '-' }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ $colspan }}" class="px-4 py-12">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none"
                                            stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                        <p class="text-sm font-semibold text-gray-500 mb-1">Tidak ada data ditemukan
                                        </p>
                                        <p class="text-xs text-gray-400">Coba ubah filter atau kriteria pencarian Anda
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($data->hasPages())
            <div class="mt-5 pt-4 border-t border-gray-100">
                {{ $data->withQueryString()->links() }}
            </div>
        @endif
    </x-card>
</x-app-layout>
