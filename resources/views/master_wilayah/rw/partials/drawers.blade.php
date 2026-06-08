@foreach ($rws as $item)
    @can('view', $item)
        <x-drawer :name="'rw-detail-' . $item->id" title="Detail RW">
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">
                        RW{{ str_pad($item->nomor_rw, 3, '0', STR_PAD_LEFT) }}</h3>
                    <p class="text-xs text-gray-400">Desa: {{ $item->desa->nama ?? '-' }}</p>
                </div>

                <div class="rounded-lg p-4 bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-sm">
                    <h4 class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-3">Informasi RW</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">Desa</p>
                            <p class="text-sm text-white">{{ $item->desa->nama ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">Nomor RW</p>
                            <p class="text-sm text-white">
                                RW{{ str_pad($item->nomor_rw, 3, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">Nama Ketua RW</p>
                            <p class="text-sm text-white">{{ $item->nama_ketua ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-white/60 uppercase tracking-widest">No. HP Ketua</p>
                            <p class="text-sm text-white">{{ $item->no_hp_ketua ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg p-4 bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-sm">
                    <h4 class="text-[11px] font-bold text-white/70 uppercase tracking-widest mb-3">Statistik</h4>
                    <div class="space-y-3">
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
                            <dd class="mt-1 text-sm text-white">{{ $item->desa->nama ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Kecamatan</dt>
                            <dd class="mt-1 text-sm text-white">{{ $item->desa->kecamatan ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Kabupaten</dt>
                            <dd class="mt-1 text-sm text-white">{{ $item->desa->kabupaten ?? '-' }}
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
                            <dt class="text-xs font-bold text-white/60 uppercase tracking-wide">Terakhir Diperbarui</dt>
                            <dd class="mt-1 text-sm text-white">
                                {{ $item->updated_at?->format('d M Y, H:i') ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </x-drawer>
    @endcan
@endforeach
