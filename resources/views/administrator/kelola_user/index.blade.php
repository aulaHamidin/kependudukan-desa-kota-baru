<x-app-layout>
    <x-slot name="title">Kelola User</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Administrator'], ['label' => 'Kelola User']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Kelola User" subtitle="Manajemen pengguna sistem.">
        </x-page-header>
    </x-slot>
    <x-alert />

    @if (old('_modal'))
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', '{{ old('_modal') }}'))"></div>
    @endif

    {{-- Summary Stats --}}
    @php
        $totalUsers = $users->count();
        $activeUsers = $users->where('is_active', true)->whereNull('deleted_at')->count();
        $inactiveUsers = $users->where('is_active', false)->whereNull('deleted_at')->count();
        $deletedUsers = $users->whereNotNull('deleted_at')->count();

        $roleStats = [
            'Super Admin' => $users->where('role', 'super_admin')->count(),
            'Admin Desa' => $users->where('role', 'admin_desa')->count(),
            'Admin RW' => $users->where('role', 'admin_rw')->count(),
            'Admin RT' => $users->where('role', 'admin_rt')->count(),
            'Viewer' => $users->where('role', 'viewer')->count(),
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($totalUsers) }}</p>
            <p class="text-xs text-gray-500">Total User</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($activeUsers) }}</p>
            <p class="text-xs text-gray-500">User Aktif</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($inactiveUsers) }}</p>
            <p class="text-xs text-gray-500">User Nonaktif</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($deletedUsers) }}</p>
            <p class="text-xs text-gray-500">User Dihapus</p>
        </div>
    </div>

    {{-- Role Distribution --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-700">Distribusi Role</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach ($roleStats as $role => $count)
                    @php
                        $roleColors = [
                            'Super Admin' => [
                                'bg' => 'bg-purple-50',
                                'text' => 'text-purple-700',
                                'border' => 'border-purple-100',
                            ],
                            'Admin Desa' => [
                                'bg' => 'bg-blue-50',
                                'text' => 'text-blue-700',
                                'border' => 'border-blue-100',
                            ],
                            'Admin RW' => [
                                'bg' => 'bg-indigo-50',
                                'text' => 'text-indigo-700',
                                'border' => 'border-indigo-100',
                            ],
                            'Admin RT' => [
                                'bg' => 'bg-cyan-50',
                                'text' => 'text-cyan-700',
                                'border' => 'border-cyan-100',
                            ],
                            'Viewer' => [
                                'bg' => 'bg-gray-50',
                                'text' => 'text-gray-700',
                                'border' => 'border-gray-100',
                            ],
                        ];
                        $colors = $roleColors[$role] ?? [
                            'bg' => 'bg-gray-50',
                            'text' => 'text-gray-700',
                            'border' => 'border-gray-100',
                        ];
                    @endphp
                    <div
                        class="flex items-center gap-3 p-3 rounded-lg {{ $colors['bg'] }} border {{ $colors['border'] }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold {{ $colors['text'] }} truncate">{{ $role }}</p>
                            <p class="text-lg font-extrabold text-gray-800">{{ $count }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @include('administrator.kelola_user.partials.table', ['users' => $users])
    @include('administrator.kelola_user.partials.drawers', ['users' => $users])
    @include('administrator.kelola_user.partials.modals', [
        'users' => $users,
        'allowedRoles' => $allowedRoles,
        'territories' => $territories,
    ])

    @push('scripts')
        <script>
            function userTableFilter() {
                return {
                    filters: {
                        role: '',
                        status: ''
                    },
                    dt: null,
                    initialized: false,
                    init() {
                        if (this.initialized) return;
                        this.initialized = true;

                        const store = this.$store?.datatables;
                        if (!store) return;

                        const instance = store.get('userTable');
                        if (instance) {
                            this.dt = instance;
                            return;
                        }

                        store.onReady('userTable', (dt) => {
                            this.dt = dt;
                        });
                    },
                    applyFilters() {
                        if (!this.dt) return;

                        const queries = [];

                        if (this.filters.role) {
                            queries.push({
                                terms: [this.filters.role],
                                columns: [1]
                            });
                        }

                        if (this.filters.status) {
                            queries.push({
                                terms: [this.filters.status],
                                columns: [3]
                            });
                        }

                        this.dt.search('', undefined, 'filters');

                        if (queries.length > 0) {
                            this.dt.multiSearch(queries, 'filters');
                        }
                    },
                    resetFilters() {
                        this.filters.role = '';
                        this.filters.status = '';

                        if (this.dt) {
                            this.dt.search('', undefined, 'filters');
                        }
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
