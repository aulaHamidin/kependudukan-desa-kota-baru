@foreach ($users as $item)
    @can('view', $item)
        <x-drawer :name="'user-detail-' . $item->id" title="Detail User">
            <div class="space-y-4">
                <div class="rounded-lg p-4 bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-sm">
                    <h4 class="text-sm font-semibold text-white">Profil</h4>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Nama</span>
                            <span class="text-white font-medium">{{ $item->name }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Username</span>
                            <span class="text-white">{{ $item->username }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Email</span>
                            <span class="text-white">
                                {{ \App\Support\Masking::email($item->email) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">NIK</span>
                            <span class="text-white">
                                {{ \App\Support\Masking::nik($item->nik) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg p-4 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-sm">
                    <h4 class="text-sm font-semibold text-white">Akses & Status</h4>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Role</span>
                            <span class="text-white">{{ $item->role_label }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Status</span>
                            @if ($item->trashed())
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">
                                    Dihapus
                                </span>
                            @elseif ($item->is_active)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    Aktif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $resolvedDesa = $item->desa?->nama
                        ?? $item->rw?->desa?->nama
                        ?? $item->rt?->rw?->desa?->nama;
                    $resolvedRw = $item->rw?->nomor_rw
                        ?? $item->rt?->rw?->nomor_rw;
                    $resolvedRt = $item->rt?->nomor_rt;
                @endphp
                <div class="rounded-lg p-4 bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-sm">
                    <h4 class="text-sm font-semibold text-white">Wilayah</h4>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Desa</span>
                            <span class="text-white">{{ $resolvedDesa ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">RW</span>
                            <span class="text-white">{{ $resolvedRw ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">RT</span>
                            <span class="text-white">{{ $resolvedRt ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg p-4 bg-gradient-to-br from-gray-500 to-gray-700 text-white shadow-sm">
                    <h4 class="text-sm font-semibold text-white">Aktivitas</h4>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Login terakhir</span>
                            <span class="text-white">{{ $item->last_login_at_label }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">IP Login</span>
                            <span class="text-white">{{ $item->last_login_ip ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-white/60">Terdaftar</span>
                            <span class="text-white">{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex items-center justify-end gap-2">
                    @if ($item->trashed())
                        @can('restore', $item)
                            <x-button type="button" variant="primary" icon="refresh"
                                x-on:click="$dispatch('open-modal', 'restore-user-{{ $item->id }}')">
                                Kembalikan
                            </x-button>
                        @endcan
                    @else
                        @can('update', $item)
                            <x-button type="button" variant="secondary" icon="edit"
                                x-on:click="$dispatch('open-modal', 'edit-user-{{ $item->id }}')">
                                Edit
                            </x-button>
                        @endcan
                        @can('update', $item)
                            @if ($item->is_active)
                                <x-button type="button" variant="warning" icon="ban"
                                    x-on:click="$dispatch('open-modal', 'disable-user-{{ $item->id }}')">
                                    Nonaktifkan
                                </x-button>
                            @else
                                <x-button type="button" variant="primary" icon="check"
                                    x-on:click="$dispatch('open-modal', 'enable-user-{{ $item->id }}')">
                                    Aktifkan
                                </x-button>
                            @endif
                        @endcan
                    @endif
                </div>
            </x-slot>
        </x-drawer>
    @endcan
@endforeach
