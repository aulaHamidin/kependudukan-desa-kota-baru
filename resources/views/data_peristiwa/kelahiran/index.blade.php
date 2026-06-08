{{-- Data Peristiwa - Kelahiran Index --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Data Peristiwa', 'url' => '#'], ['label' => 'Kelahiran']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Event Kelahiran" subtitle="Kelola data kelahiran bayi di desa">
            <x-slot name="actions">
                @can('create', App\Models\Event::class)
                    <x-button variant="primary" icon="plus" :href="route('events.kelahiran.create')">
                        Tambah Event
                    </x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total" :value="$stats['total']" icon="document-text" color="blue" />
        <x-stat-card label="Draf" :value="$stats['draft']" icon="pencil-alt" color="yellow" />
        <x-stat-card label="Terverifikasi" :value="$stats['verified']" icon="check-circle" color="green" />
        <x-stat-card label="Dibatalkan" :value="$stats['void']" icon="ban" color="red" />
    </div>

    {{-- Filter --}}
    {{-- Filter Section --}}
    <x-card class="mb-6">
        <div class="p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Filter Event Kelahiran</h3>
            </div>

            <form method="GET" action="{{ route('events.kelahiran.index') }}">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cari</label>
                        <input type="search" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama atau NIK..." class="form-input-custom w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select name="status_data" class="form-select-custom w-full text-sm">
                            <option value="">Semua Status</option>
                            <option value="DRAFT" {{ request('status_data') === 'DRAFT' ? 'selected' : '' }}>Draf
                            </option>
                            <option value="VERIFIED" {{ request('status_data') === 'VERIFIED' ? 'selected' : '' }}>
                                Terverifikasi</option>
                            <option value="VOID" {{ request('status_data') === 'VOID' ? 'selected' : '' }}>Dibatalkan
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="form-input-custom w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="form-input-custom w-full text-sm" />
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <x-button type="submit" variant="primary" class="px-6">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Terapkan Filter
                    </x-button>
                    @if (request()->anyFilled(['search', 'status_data', 'start_date', 'end_date']))
                        <a href="{{ route('events.kelahiran.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </x-card>

    {{-- Active Filters --}}
    @php
        $activeFilters = [];
        if (request('search')) {
            $activeFilters[] = ['label' => 'Pencarian', 'value' => request('search')];
        }
        if (request('status_data')) {
            $statusLabels = ['DRAFT' => 'Draf', 'VERIFIED' => 'Terverifikasi', 'VOID' => 'Dibatalkan'];
            $activeFilters[] = [
                'label' => 'Status',
                'value' => $statusLabels[request('status_data')] ?? request('status_data'),
            ];
        }
        if (request('start_date')) {
            $activeFilters[] = ['label' => 'Dari', 'value' => request('start_date')];
        }
        if (request('end_date')) {
            $activeFilters[] = ['label' => 'Sampai', 'value' => request('end_date')];
        }
    @endphp
    @if (count($activeFilters) > 0)
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-xs font-semibold text-gray-500">Filter Aktif:</span>
            @foreach ($activeFilters as $filter)
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $filter['label'] }}: <span class="font-bold">{{ Str::limit($filter['value'], 30) }}</span>
                </span>
            @endforeach
            <a href="{{ route('events.kelahiran.index') }}"
                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Hapus Semua
            </a>
        </div>
    @endif

    {{-- Table --}}
    <x-data-table class="min-w-full" :searchable="false">
        <x-slot name="head">
            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Bayi</th>
            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis Kelamin</th>
            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Orang Tua</th>
            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status Kelahiran</th>
            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status Event</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
        </x-slot>

        @forelse($events as $event)
            @php
                $kelahiran = $event->eventKelahiran;
            @endphp
            <tr class="hover:bg-gray-50/70">
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    {{ $event->event_date?->format('d/m/Y') ?? '-' }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        {{ $kelahiran->nama_bayi ?? '-' }}
                    </div>
                    @if ($event->penduduk)
                        <div class="text-xs text-gray-500">
                            NIK: <span class="data-nik">{{ \App\Support\Masking::nik($event->penduduk->nik) }}</span>
                        </div>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                    {{ $kelahiran->jenis_kelamin === 'L' ? 'Laki-laki' : ($kelahiran->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                    <div class="space-y-1">
                        @if ($kelahiran->ayah)
                            <div>
                                <span class="text-xs text-gray-500">Ayah:</span>
                                {{ $kelahiran->ayah->nama_lengkap }}
                            </div>
                        @elseif ($kelahiran->nama_ayah)
                            <div>
                                <span class="text-xs text-gray-500">Ayah:</span>
                                {{ $kelahiran->nama_ayah }}
                            </div>
                        @endif

                        @if ($kelahiran->ibu)
                            <div>
                                <span class="text-xs text-gray-500">Ibu:</span>
                                {{ $kelahiran->ibu->nama_lengkap }}
                            </div>
                        @elseif ($kelahiran->nama_ibu)
                            <div>
                                <span class="text-xs text-gray-500">Ibu:</span>
                                {{ \App\Support\Masking::text($kelahiran->nama_ibu ?? '') }}
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @if ($kelahiran->status_kelahiran->value === 'HIDUP')
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">
                            <i class="fas fa-heart mr-1"></i> Hidup
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-gray-50 text-gray-700 border-gray-200">
                            <i class="fas fa-times-circle mr-1"></i> Lahir Mati
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    @include('data_peristiwa.partials.status-badge', [
                        'status' => $event->status_data,
                    ])
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                        @can('view', $event)
                            <x-button variant="secondary" size="sm" :href="route('events.kelahiran.show', $event)">
                                Detail
                            </x-button>
                        @endcan
                        @can('update', $event)
                            <x-button variant="warning" size="sm" :href="route('events.kelahiran.edit', $event)">
                                Edit
                            </x-button>
                        @endcan
                        @can('delete', $event)
                            <x-delete-confirm :action="route('events.kelahiran.destroy', $event)" title="Hapus Event Kelahiran?"
                                text="Data event kelahiran ini akan dihapus permanen.">
                                <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                            </x-delete-confirm>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-12">
                    <x-empty-state title="Belum ada event kelahiran"
                        description="Klik tombol 'Tambah Event' untuk mencatat kelahiran bayi baru." />
                </td>
            </tr>
        @endforelse

        @if ($events->hasPages())
            <x-slot name="footer">
                {{ $events->withQueryString()->links() }}
            </x-slot>
        @endif
    </x-data-table>
</x-app-layout>
