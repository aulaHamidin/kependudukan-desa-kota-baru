{{-- Data Inti - Kartu Keluarga Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Kependudukan', 'url' => '#'],
            ['label' => 'Kartu Keluarga', 'url' => route('kartu-keluarga.index')],
            ['label' => \App\Support\Masking::nik($kartuKeluarga->no_kk)],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail Kartu Keluarga" subtitle="Ringkasan data kartu keluarga.">
            <x-slot name="actions">
                @can('update', $kartuKeluarga)
                    <x-button variant="secondary" icon="edit" :href="route('kartu-keluarga.edit', $kartuKeluarga)">
                        Edit
                    </x-button>
                @endcan
                @can('delete', $kartuKeluarga)
                    <x-delete-confirm :action="route('kartu-keluarga.destroy', $kartuKeluarga)" title="Hapus Kartu Keluarga?"
                        text="Apakah Anda yakin ingin menghapus KK ini? Tindakan ini tidak dapat dibatalkan.">
                        <x-button type="submit" variant="danger" icon="trash">
                            Hapus
                        </x-button>
                    </x-delete-confirm>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    @if (auth()->user()?->hasRole('viewer'))
        <x-alert type="info" class="mb-6">
            <strong>Mode Lihat-Saja:</strong> Anda hanya dapat melihat data, tidak dapat melakukan perubahan.
        </x-alert>
    @endif

    @php
        $statusColor = match ($kartuKeluarga->status_kk) {
            'AKTIF' => 'from-emerald-500 to-teal-700',
            'NON-AKTIF', 'PINDAH' => 'from-amber-500 to-amber-700',
            default => 'from-blue-600 to-blue-800',
        };
        $kepalaKeluarga = $kartuKeluarga->kkMembers->firstWhere('is_kepala_keluarga', true);
    @endphp

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-br {{ $statusColor }} rounded-xl p-5 sm:p-6 text-white shadow-lg mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    {{-- Home/Family Icon --}}
                    <div class="w-10 h-10 rounded-lg bg-white/15 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold font-mono tracking-wide">
                            {{ \App\Support\Masking::nik($kartuKeluarga->no_kk) }}</h2>
                        <p class="text-white/70 text-sm">Kartu Keluarga</p>
                    </div>
                </div>
                <p class="text-white/80 text-sm mt-1">
                    @if ($kepalaKeluarga)
                        Kepala Keluarga: <span
                            class="font-semibold text-white">{{ $kepalaKeluarga->penduduk?->nama_lengkap ?? '-' }}</span>
                    @else
                        Kepala Keluarga belum ditetapkan
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-5">
                <div class="text-center sm:text-right">
                    <div class="text-xs text-white/60 uppercase tracking-wide mb-0.5">Status</div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold
                        {{ $kartuKeluarga->status_kk === 'AKTIF' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-800' }}">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $kartuKeluarga->status_kk === 'AKTIF' ? 'bg-green-300' : 'bg-red-500' }}"></span>
                        {{ $kartuKeluarga->status_kk }}
                    </span>
                </div>
                <div class="text-center sm:text-right">
                    <div class="text-xs text-white/60 uppercase tracking-wide mb-0.5">Anggota</div>
                    <div class="text-2xl font-bold">{{ $kartuKeluarga->kkMembers->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Info Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Tanggal Terbentuk</p>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $kartuKeluarga->tanggal_terbentuk?->format('d F Y') ?? '-' }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Desa</p>
                <p class="text-sm font-semibold text-gray-900">{{ $kartuKeluarga->rt?->rw?->desa?->nama ?? '-' }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Wilayah RT/RW</p>
                <p class="text-sm font-semibold text-gray-900">RT {{ $kartuKeluarga->rt?->nomor_rt ?? '-' }} / RW
                    {{ $kartuKeluarga->rt?->rw?->nomor_rw ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Alamat --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Alamat Lengkap</h3>
        </div>
        <div class="px-5 py-4">
            <p class="text-sm text-gray-800 leading-relaxed">{{ $kartuKeluarga->alamat ?? '-' }}</p>
        </div>
    </div>

    {{-- Anggota KK --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6" x-data="{}">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Anggota Keluarga</h3>
                    <p class="text-xs text-gray-500">{{ $kartuKeluarga->kkMembers->count() }} anggota terdaftar</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80">
                        <x-table-header class="pl-5">Nama</x-table-header>
                        <x-table-header>Hubungan</x-table-header>
                        <x-table-header>Status</x-table-header>
                        <x-table-header>Tanggal Masuk</x-table-header>
                        <x-table-header class="text-center">Aksi</x-table-header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($kartuKeluarga->kkMembers as $member)
                        <tr class="table-row-hover">
                            <x-table-cell class="pl-5">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar --}}
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $member->is_kepala_keluarga ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ strtoupper(substr($member->penduduk?->nama_lengkap ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('penduduk.show', $member->penduduk_id) }}"
                                            class="text-primary-600 hover:underline font-medium">
                                            {{ $member->penduduk?->nama_lengkap ?? '-' }}
                                        </a>
                                        @if ($member->is_kepala_keluarga)
                                            <span
                                                class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">
                                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                                </svg>
                                                Kepala
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </x-table-cell>
                            <x-table-cell>{{ $member->hubunganKeluarga?->nama ?? '-' }}</x-table-cell>
                            <x-table-cell>
                                <x-badge :type="$member->status === 'AKTIF' ? 'aktif' : 'non-aktif'">
                                    {{ $member->status }}
                                </x-badge>
                            </x-table-cell>
                            <x-table-cell>{{ $member->tanggal_masuk?->format('d/m/Y') ?? '-' }}</x-table-cell>
                            <x-table-cell class="text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @can('update', $member)
                                        @if ($member->status === 'AKTIF' && !$member->is_kepala_keluarga)
                                            <button type="button" class="btn-action btn-action-view"
                                                title="Set sebagai Kepala Keluarga"
                                                @click="$dispatch('open-modal', 'set-kepala-{{ $member->id }}')">
                                                <x-button-icon icon="user-check" class="w-4 h-4" />
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </x-table-cell>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8">
                                <x-empty-state title="Belum ada anggota"
                                    description="Kartu keluarga ini belum memiliki anggota. Tambahkan anggota untuk melengkapi data KK." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modals for add/leave member --}}
    @include('data_inti.kartu_keluarga.partials.modals')
</x-app-layout>
