<x-card :padding="false">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar User</h3>
                    <p class="text-sm text-gray-500">Kelola {{ $users->count() }} pengguna sistem</p>
                </div>
            </div>
            <div x-data="userTableFilter()" x-init="init()" class="flex gap-2 items-end flex-wrap">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Role</label>
                        <select x-model="filters.role" x-on:change="applyFilters()"
                            class="form-select-custom w-full text-sm">
                            <option value="">Semua Role</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Admin Desa">Admin Desa</option>
                            <option value="Admin RW">Admin RW</option>
                            <option value="Admin RT">Admin RT</option>
                            <option value="Viewer">Viewer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select x-model="filters.status" x-on:change="applyFilters()"
                            class="form-select-custom w-full text-sm">
                            <option value="">Semua Status</option>
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                            <option value="Dihapus">Dihapus</option>
                        </select>
                    </div>
                </div>
                <button type="button" x-on:click="resetFilters()" class="btn btn-ghost px-3 py-2 text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </button>
                @can('create', \App\Models\User::class)
                    <x-button type="button" icon="plus" x-on:click="$dispatch('open-modal', 'create-user')">
                        Tambah User
                    </x-button>
                @endcan
            </div>
        </div>
    </x-slot>

    <x-data-table :datatable="true" :datatableOptions="[
        'perPage' => 10,
        'perPageSelect' => [5, 10, 25, 50, 100],
        'searchable' => true,
        'paging' => true,
        'labels' => [
            'placeholder' => 'Cari nama, username, atau NIK...',
            'perPage' => 'Data per halaman',
            'noRows' => 'Tidak ada data',
            'noResults' => 'Tidak ada hasil untuk pencarian ini.',
            'info' => 'Menampilkan {start} - {end} dari {rows} data',
        ],
    ]" id="userTable">

        <x-slot name="head">
            <tr>
                <x-table-header>Pengguna</x-table-header>
                <x-table-header>Role</x-table-header>
                <x-table-header>Wilayah</x-table-header>
                <x-table-header>Status</x-table-header>
                <x-table-header class="text-center">Aksi</x-table-header>
            </tr>
        </x-slot>

        @forelse ($users as $item)
            @php
                $editModal = 'edit-user-' . $item->id;
            @endphp
            <tr class="table-row-hover">
                <x-table-cell>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                            {{ strtoupper(substr($item->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                            <p class="text-xs text-gray-500">{{ $item->username }}</p>
                        </div>
                    </div>
                </x-table-cell>
                <x-table-cell>
                    @php
                        $roleColors = [
                            'Super Admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                            'Admin Desa' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'Admin RW' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'Admin RT' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                            'Viewer' => 'bg-gray-100 text-gray-700 border-gray-200',
                        ];
                        $roleColor = $roleColors[$item->role_label] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    @endphp
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $roleColor }}">
                        {{ $item->role_label }}
                    </span>
                </x-table-cell>
                <x-table-cell>
                    @if ($item->desa)
                        <div class="flex items-center gap-1.5 text-sm text-gray-700">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <span>{{ $item->desa->nama }}</span>
                        </div>
                    @endif
                    @if ($item->rw)
                        <div class="flex items-center gap-1.5 text-sm text-gray-700 mt-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>RW {{ $item->rw->nomor_rw }}</span>
                        </div>
                    @endif
                    @if ($item->rt)
                        <div class="flex items-center gap-1.5 text-sm text-gray-700 mt-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>RT {{ $item->rt->nomor_rt }}</span>
                        </div>
                    @endif
                    @if (!$item->desa && !$item->rw && !$item->rt)
                        <span class="text-sm text-gray-400">-</span>
                    @endif
                </x-table-cell>
                <x-table-cell>
                    @if ($item->trashed())
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Dihapus
                        </span>
                    @elseif ($item->is_active)
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600 border border-gray-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            Nonaktif
                        </span>
                    @endif
                </x-table-cell>
                <x-table-cell class="text-center">
                    <div class="flex items-center justify-center gap-1">
                        @can('view', $item)
                            <button type="button" class="btn-action btn-action-view" title="Detail"
                                x-on:click="$dispatch('open-drawer', 'user-detail-{{ $item->id }}')">
                                <x-button-icon icon="eye" class="w-4 h-4" />
                            </button>
                        @endcan
                        @if ($item->trashed())
                            @can('restore', $item)
                                <button type="button" class="btn-action btn-action-verify" title="Kembalikan"
                                    x-on:click="$dispatch('open-modal', 'restore-user-{{ $item->id }}')">
                                    <x-button-icon icon="refresh" class="w-4 h-4" />
                                </button>
                            @endcan
                        @else
                            @can('update', $item)
                                <button type="button" class="btn-action btn-action-edit" title="Edit"
                                    x-on:click="$dispatch('open-modal', '{{ $editModal }}')">
                                    <x-button-icon icon="edit" class="w-4 h-4" />
                                </button>
                            @endcan
                            @can('delete', $item)
                                <button type="button" class="btn-action btn-action-delete" title="Hapus"
                                    x-on:click="$dispatch('open-modal', 'delete-user-{{ $item->id }}')">
                                    <x-button-icon icon="delete" class="w-4 h-4" />
                                </button>
                            @endcan
                            @can('update', $item)
                                @if ($item->is_active)
                                    <button type="button" class="btn-action btn-action-edit" title="Nonaktifkan"
                                        x-on:click="$dispatch('open-modal', 'disable-user-{{ $item->id }}')">
                                        <x-button-icon icon="ban" class="w-4 h-4" />
                                    </button>
                                @else
                                    <button type="button" class="btn-action btn-action-verify" title="Aktifkan"
                                        x-on:click="$dispatch('open-modal', 'enable-user-{{ $item->id }}')">
                                        <x-button-icon icon="check" class="w-4 h-4" />
                                    </button>
                                @endif
                            @endcan
                        @endif
                    </div>
                </x-table-cell>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-empty-state title="Belum Ada User" description="Belum ada data user yang ditampilkan."
                        icon="empty" />
                </td>
            </tr>
        @endforelse
    </x-data-table>
</x-card>
