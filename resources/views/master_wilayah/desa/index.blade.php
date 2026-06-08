<x-app-layout>
    <x-slot name="title">Data Desa</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Wilayah'], ['label' => 'Desa']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Data Desa" subtitle="Kelola data desa dalam sistem">
        </x-page-header>
    </x-slot>

    <x-alert />

    @if (old('_modal'))
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', '{{ old('_modal') }}'))"></div>
    @endif

    <x-card :padding="false">
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Desa</h3>
                    <p class="text-sm text-gray-500">Total {{ $desa->count() }} data desa</p>
                </div>
                <div x-data>
                    @can('create', \App\Models\Desa::class)
                        <button type="button" class="btn btn-primary" x-on:click="$dispatch('open-modal', 'create-desa')">
                            <x-button-icon icon="plus" />
                            Tambah Desa
                        </button>
                    @endcan
                </div>
            </div>
        </x-slot>

        <x-data-table :datatable="true" :datatableOptions="[
            'perPage' => 10,
            'perPageSelect' => [10, 25, 50, 100],
            'searchable' => true,
            'paging' => true,
            'labels' => [
                'placeholder' => 'Cari kode/nama/kecamatan...',
                'perPage' => 'data per halaman',
                'noRows' => 'Tidak ada data',
                'noResults' => 'Tidak ada hasil untuk pencarian ini.',
                'info' => 'Menampilkan {start} - {end} dari {rows} data',
            ],
        ]" id="desaTable">


            <x-slot name="head">
                <tr>
                    <x-table-header>Kode</x-table-header>
                    <x-table-header>Nama</x-table-header>
                    <x-table-header>Kecamatan</x-table-header>
                    <x-table-header>Kabupaten</x-table-header>
                    <x-table-header>Provinsi</x-table-header>
                    <x-table-header>Kode Pos</x-table-header>
                    <x-table-header class="text-center">Aksi</x-table-header>
                </tr>
            </x-slot>

            @forelse ($desa as $item)
                @php
                    $editModal = 'edit-desa-' . $item->id;
                @endphp
                <tr class="table-row-hover">
                    <x-table-cell class="font-medium text-gray-900">
                        {{ $item->kode_desa }}
                    </x-table-cell>
                    <x-table-cell>{{ $item->nama }}</x-table-cell>
                    <x-table-cell>{{ $item->kecamatan }}</x-table-cell>
                    <x-table-cell>{{ $item->kabupaten }}</x-table-cell>
                    <x-table-cell>{{ $item->provinsi }}</x-table-cell>
                    <x-table-cell>{{ $item->kode_pos ?? '-' }}</x-table-cell>
                    <x-table-cell class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            @can('view', $item)
                                <button type="button" class="btn-action btn-action-view" title="Lihat Detail"
                                    x-on:click="$dispatch('open-drawer', 'desa-detail-{{ $item->id }}')">
                                    <x-button-icon icon="eye" class="w-4 h-4" />
                                </button>
                            @endcan
                            @can('update', $item)
                                <button type="button" class="btn-action btn-action-edit" title="Edit"
                                    x-on:click="$dispatch('open-modal', '{{ $editModal }}')">
                                    <x-button-icon icon="edit" class="w-4 h-4" />
                                </button>
                            @endcan
                            @can('delete', $item)
                                <button type="button" class="btn-action btn-action-delete" title="Hapus"
                                    x-on:click="$dispatch('open-modal', 'delete-desa-{{ $item->id }}')">
                                    <x-button-icon icon="delete" class="w-4 h-4" />
                                </button>
                            @endcan
                        </div>
                    </x-table-cell>
                </tr>

            @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state title="Belum Ada Data Desa" description="Data desa belum tersedia dalam sistem."
                            icon="empty">
                            @can('create', \App\Models\Desa::class)
                                <button type="button" class="btn btn-primary mt-4"
                                    x-on:click="$dispatch('open-modal', 'create-desa')">
                                    <x-button-icon icon="plus" />
                                    Tambah Desa
                                </button>
                            @endcan
                        </x-empty-state>
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    @foreach ($desa as $item)
        @php
            $editModal = 'edit-desa-' . $item->id;
            $useOld = old('_modal') === $editModal;
        @endphp

        @can('update', $item)
            <x-modal.form :name="$editModal" title="Edit Desa" subtitle="Perbarui data desa {{ $item->nama }}.">
                <form id="{{ $editModal }}-form" method="POST"
                    action="{{ route('master.wilayah.desa.update', $item) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_modal" value="{{ $editModal }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-form-input name="kode_desa" label="Kode Desa" required :value="$useOld ? old('kode_desa', $item->kode_desa) : $item->kode_desa" :useOld="$useOld"
                            helper="Kode unik untuk identifikasi desa (maks. 20 karakter)" />

                        <x-form-input name="nama" label="Nama Desa" required :value="$useOld ? old('nama', $item->nama) : $item->nama" :useOld="$useOld"
                            helper="Nama resmi desa (maks. 100 karakter)" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-form-input name="kecamatan" label="Kecamatan" required :value="$useOld ? old('kecamatan', $item->kecamatan) : $item->kecamatan" :useOld="$useOld" />

                        <x-form-input name="kabupaten" label="Kabupaten/Kota" required :value="$useOld ? old('kabupaten', $item->kabupaten) : $item->kabupaten"
                            :useOld="$useOld" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-form-input name="provinsi" label="Provinsi" required :value="$useOld ? old('provinsi', $item->provinsi) : $item->provinsi" :useOld="$useOld" />

                        <x-form-input name="kode_pos" label="Kode Pos" required :value="$useOld ? old('kode_pos', $item->kode_pos) : $item->kode_pos" :useOld="$useOld"
                            helper="Kode pos desa (maks. 10 karakter)" />
                    </div>
                </form>

                <x-slot name="footer">
                    <x-button type="button" variant="secondary"
                        x-on:click="$dispatch('close-modal', '{{ $editModal }}')">
                        Batal
                    </x-button>
                    <x-button type="submit" icon="save" form="{{ $editModal }}-form">
                        Simpan Perubahan
                    </x-button>
                </x-slot>
            </x-modal.form>
        @endcan

        @can('view', $item)
            <x-drawer :name="'desa-detail-' . $item->id" title="Detail Desa">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">{{ $item->nama }}</h3>
                        <p class="text-xs text-gray-400">Kode: {{ $item->kode_desa }}</p>
                    </div>

                    <div class="rounded-lg p-4 bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-sm">
                        <h4 class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-3">Informasi Desa</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">Kode Desa</p>
                                <p class="text-sm text-white">{{ $item->kode_desa }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">Nama Desa</p>
                                <p class="text-sm text-white">{{ $item->nama }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg p-4 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-sm">
                        <h4 class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-3">Statistik</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white/15 rounded-lg">
                                <span class="text-sm text-white/80">Total RW</span>
                                <span class="text-lg font-bold text-white">
                                    {{ $item->rws_count ?? 0 }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white/15 rounded-lg">
                                <span class="text-sm text-white/80">Total RT</span>
                                <span class="text-lg font-bold text-white">
                                    {{ $item->rts_count ?? 0 }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white/15 rounded-lg">
                                <span class="text-sm text-white/80">Total Admin</span>
                                <span class="text-lg font-bold text-white">
                                    {{ $item->users_count ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg p-4 bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-sm">
                        <h4 class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-3">Lokasi</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Desa</dt>
                                <dd class="mt-1 text-sm text-white">{{ $item->nama }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Kecamatan</dt>
                                <dd class="mt-1 text-sm text-white">{{ $item->kecamatan }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Kabupaten</dt>
                                <dd class="mt-1 text-sm text-white">{{ $item->kabupaten }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Provinsi</dt>
                                <dd class="mt-1 text-sm text-white">{{ $item->provinsi }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Kode Pos</dt>
                                <dd class="mt-1 text-sm text-white">{{ $item->kode_pos ?? '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg p-4 bg-gradient-to-br from-gray-500 to-gray-700 text-white shadow-sm">
                        <h4 class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-3">Info Sistem</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Dibuat</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ $item->created_at?->format('d M Y, H:i') ?? '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Terakhir Diperbarui
                                </dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ $item->updated_at?->format('d M Y, H:i') ?? '-' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </x-drawer>
        @endcan

        @can('delete', $item)
            <x-modal.confirm :name="'delete-desa-' . $item->id" title="Hapus Desa?"
                description="Data desa {{ $item->nama }} akan dihapus. Pastikan tidak ada RW yang terkait."
                :action="route('master.wilayah.desa.destroy', $item)" />
        @endcan
    @endforeach

    @can('create', \App\Models\Desa::class)
        <x-modal.form name="create-desa" title="Tambah Desa" subtitle="Tambahkan data desa baru ke dalam sistem.">
            <form id="create-desa-form" method="POST" action="{{ route('master.wilayah.desa.store') }}"
                class="space-y-6">
                @csrf
                <input type="hidden" name="_modal" value="create-desa">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form-input name="kode_desa" label="Kode Desa" placeholder="Contoh: 3507010001" required
                        helper="Kode unik untuk identifikasi desa (maks. 20 karakter)" />

                    <x-form-input name="nama" label="Nama Desa" placeholder="Contoh: Sukamaju" required
                        helper="Nama resmi desa (maks. 100 karakter)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form-input name="kecamatan" label="Kecamatan" placeholder="Contoh: Kepanjen" required />

                    <x-form-input name="kabupaten" label="Kabupaten/Kota" placeholder="Contoh: Malang" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form-input name="provinsi" label="Provinsi" placeholder="Contoh: Jawa Timur" required />

                    <x-form-input name="kode_pos" label="Kode Pos" placeholder="Contoh: 65163" required
                        helper="Kode pos desa (maks. 10 karakter)" />
                </div>
            </form>

            <x-slot name="footer">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-desa')">
                    Batal
                </x-button>
                <x-button type="submit" icon="save" form="create-desa-form">
                    Simpan Desa
                </x-button>
            </x-slot>
        </x-modal.form>
    @endcan
</x-app-layout>
