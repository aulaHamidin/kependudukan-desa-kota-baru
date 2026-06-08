<aside id="sidebar" class="sidebar sidebar-glass fixed top-0 left-0 z-40 w-64 h-screen shadow-sidebar"
    x-data="{
        openMenus: {
            masterWilayah: {{ request()->routeIs('master.wilayah.*') ? 'true' : 'false' }},
            masterReferences: {{ request()->routeIs('master.agama.*', 'master.pendidikan.*', 'master.pekerjaan.*', 'master.golongan_darah.*', 'master.pendapatan_range.*', 'master.status.*', 'master.hubungan_keluarga.*', 'master.event_type.*') ? 'true' : 'false' }},
            administrator: {{ request()->routeIs('administrator.*') ? 'true' : 'false' }}
        }
    }">
    <div class="h-full flex flex-col px-3 py-4 overflow-y-auto scrollbar-thin">
        {{-- Logo & Brand --}}
        @php
            $sidebarDesaName =
                auth()->user()?->desa?->nama ??
                (auth()->user()?->rw?->desa?->nama ?? (auth()->user()?->rt?->rw?->desa?->nama ?? 'KOTA BARU'));
        @endphp
        <a href="{{ route('dashboard') }}"
            class="flex flex-col items-center gap-3 px-3 py-3 mb-4 border-b border-blue-400/30 pb-4">
            <img src="{{ asset('images/logo-desa.png') }}" alt="Logo Desa"
                class="w-9 h-9 rounded-md object-contain bg-white/20 p-0.5">
            <div class="text-center w-full">
                <span class="block text-base font-bold text-white">SIAK-DESA</span>
                <span class="block text-base font-bold text-white">{{ $sidebarDesaName }}</span>
            </div>
        </a>
        </a>

        {{-- Navigation --}}
        <nav class="flex-1 space-y-1">
            {{-- ─── BERANDA ─── --}}
            <p class="px-3 mb-2 text-[10px] font-semibold text-blue-300/70 uppercase tracking-wider">
                Beranda</p>

            <x-sidebar-menu :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard">
                Dashboard
            </x-sidebar-menu>

            {{-- ─── MASTER DATA ─── --}}
            <div class="pt-4 pb-2">
                <p class="px-3 mb-2 text-[10px] font-semibold text-blue-300/70 uppercase tracking-wider">
                    Master Data</p>
            </div>

            @can('viewAny', \App\Models\Desa::class)
                <x-sidebar-dropdown id="masterWilayah" title="Wilayah" icon="map" :active="request()->routeIs('master.wilayah.*')">
                    @can('viewAny', \App\Models\Desa::class)
                        <x-sidebar-menu :href="route('master.wilayah.desa.index')" :active="request()->routeIs('master.wilayah.desa.*')" size="sm">
                            Desa
                        </x-sidebar-menu>
                    @endcan
                    @can('viewAny', \App\Models\Rw::class)
                        <x-sidebar-menu :href="route('master.wilayah.rw.index')" :active="request()->routeIs('master.wilayah.rw.*')" size="sm">
                            RW
                        </x-sidebar-menu>
                    @endcan
                    @can('viewAny', \App\Models\Rt::class)
                        <x-sidebar-menu :href="route('master.wilayah.rt.index')" :active="request()->routeIs('master.wilayah.rt.*')" size="sm">
                            RT
                        </x-sidebar-menu>
                    @endcan
                </x-sidebar-dropdown>
            @endcan

            @if (auth()->user()?->hasRole('super_admin'))
                <x-sidebar-dropdown id="masterReferences" title="Referensi" icon="database" :active="request()->routeIs(
                    'master.agama.*',
                    'master.pendidikan.*',
                    'master.pekerjaan.*',
                    'master.golongan_darah.*',
                    'master.pendapatan_range.*',
                    'master.status.*',
                    'master.hubungan_keluarga.*',
                    'master.event_type.*',
                    'master.jenis_surat.*',
                )">
                    <x-sidebar-menu :href="route('master.agama.index')" :active="request()->routeIs('master.agama.*')" size="sm">
                        Agama
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.pendidikan.index')" :active="request()->routeIs('master.pendidikan.*')" size="sm">
                        Pendidikan
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.pekerjaan.index')" :active="request()->routeIs('master.pekerjaan.*')" size="sm">
                        Pekerjaan
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.golongan_darah.index')" :active="request()->routeIs('master.golongan_darah.*')" size="sm">
                        Golongan Darah
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.pendapatan_range.index')" :active="request()->routeIs('master.pendapatan_range.*')" size="sm">
                        Range Pendapatan
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.status.index')" :active="request()->routeIs('master.status.*')" size="sm">
                        Status Kependudukan
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.hubungan_keluarga.index')" :active="request()->routeIs('master.hubungan_keluarga.*')" size="sm">
                        Hubungan Keluarga
                    </x-sidebar-menu>
                    <x-sidebar-menu :href="route('master.event_type.index')" :active="request()->routeIs('master.event_type.*')" size="sm">
                        Tipe Event
                    </x-sidebar-menu>
                    @can('viewAny', \App\Models\JenisSurat::class)
                        <x-sidebar-menu :href="route('master.jenis_surat.index')" :active="request()->routeIs('master.jenis_surat.*')" size="sm">
                            Jenis Surat
                        </x-sidebar-menu>
                    @endcan
                </x-sidebar-dropdown>
            @endif

            {{-- ─── KEPENDUDUKAN ─── --}}
            <div class="pt-4 pb-2">
                <p class="px-3 mb-2 text-[10px] font-semibold text-blue-300/70 uppercase tracking-wider">
                    Kependudukan</p>
            </div>

            @can('viewAny', \App\Models\Penduduk::class)
                <x-sidebar-menu :href="route('penduduk.index')" :active="request()->routeIs('penduduk.*')" icon="user">
                    Penduduk
                </x-sidebar-menu>
            @endcan
            @can('viewAny', \App\Models\KartuKeluarga::class)
                <x-sidebar-menu :href="route('kartu-keluarga.index')" :active="request()->routeIs('kartu-keluarga.*')" icon="home">
                    Kartu Keluarga
                </x-sidebar-menu>
            @endcan
            {{-- @if (auth()->user()?->hasRole('admin_desa'))
                <x-sidebar-menu :href="route('penduduk.import.index')" :active="request()->routeIs('penduduk.import.*')" icon="document">
                    Import Penduduk
                </x-sidebar-menu>
            @endif --}}

            {{-- ─── PERISTIWA ─── --}}
            @can('viewAny', \App\Models\Event::class)
                <div class="pt-4 pb-2">
                    <p class="px-3 mb-2 text-[10px] font-semibold text-blue-300/70 uppercase tracking-wider">
                        Peristiwa</p>
                </div>

                <x-sidebar-menu :href="route('events.kelahiran.index')" :active="request()->routeIs('events.kelahiran.*')" icon="birth">
                    Kelahiran
                </x-sidebar-menu>
                <x-sidebar-menu :href="route('events.kematian.index')" :active="request()->routeIs('events.kematian.*')" icon="death">
                    Kematian
                </x-sidebar-menu>
                <x-sidebar-menu :href="route('events.pindah.index')" :active="request()->routeIs('events.pindah.*')" icon="arrow-right">
                    Pindah
                </x-sidebar-menu>
                <x-sidebar-menu :href="route('events.datang.index')" :active="request()->routeIs('events.datang.*')" icon="arrow-left">
                    Datang
                </x-sidebar-menu>
                <x-sidebar-menu :href="route('approvals.index')" :active="request()->routeIs('approvals.*')" icon="check-circle">
                    Approvals
                </x-sidebar-menu>
            @endcan

            {{-- ─── LAYANAN ─── --}}
            @if (auth()->user()?->hasRole('admin_desa') || auth()->user()?->hasRole('super_admin'))
                <div class="pt-4 pb-2">
                    <p class="px-3 mb-2 text-[10px] font-semibold text-blue-300/70 uppercase tracking-wider">
                        Layanan</p>
                </div>

                @can('viewAny', \App\Models\SuratTerbit::class)
                    <x-sidebar-menu :href="route('surat.terbit.index')" :active="request()->routeIs('surat.terbit.*')" icon="document">
                        Surat Terbit
                    </x-sidebar-menu>
                @endcan

                @if (auth()->user()?->hasRole('admin_desa'))
                    <x-sidebar-menu :href="route('pindah-rt.index')" :active="request()->routeIs('pindah-rt.*')" icon="switch-horizontal">
                        Pindah RT
                    </x-sidebar-menu>
                @endif
            @endif

            {{-- ─── SISTEM ─── --}}
            @can('viewAny', \App\Models\User::class)
                <div class="pt-4 pb-2">
                    <p class="px-3 mb-2 text-[10px] font-semibold text-blue-300/70 uppercase tracking-wider">
                        Sistem</p>
                </div>

                <x-sidebar-dropdown id="administrator" title="Administrator" icon="cog" :active="request()->routeIs('administrator.*')">
                    @can('viewAny', \App\Models\User::class)
                        <x-sidebar-menu :href="route('administrator.kelola-user.index')" :active="request()->routeIs('administrator.kelola-user.*')" size="sm">
                            Kelola User
                        </x-sidebar-menu>
                    @endcan
                    @if (auth()->user()?->hasRole('super_admin') || auth()->user()?->hasRole('admin_desa'))
                        <x-sidebar-menu :href="route('administrator.audit-log.index')" :active="request()->routeIs('administrator.audit-log.*')" size="sm">
                            Audit Log
                        </x-sidebar-menu>
                    @endif
                    @if (auth()->user()?->hasRole('admin_desa'))
                        <x-sidebar-menu :href="route('administrator.reporting.index')" :active="request()->routeIs('administrator.reporting.*')" size="sm">
                            View & Reporting
                        </x-sidebar-menu>
                    @endif
                </x-sidebar-dropdown>
            @endcan
        </nav>

        {{-- Bottom Card --}}
        <div class="mt-4 p-3 rounded-md bg-white/10 border border-blue-400/20 text-blue-100">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                </svg>
                <span class="text-xs font-semibold">SIAK-Desa v1.0</span>
            </div>
            <p class="text-[11px] text-blue-200/60 leading-relaxed">Sistem Informasi Administrasi Kependudukan.</p>
        </div>
    </div>
</aside>
