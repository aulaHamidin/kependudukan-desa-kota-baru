<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Administrator', 'url' => '#'], ['label' => 'Audit Log']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Audit Log" subtitle="Catatan aktivitas sistem untuk admin.">
        </x-page-header>
    </x-slot>

    <x-alert />

    {{-- Summary Stats --}}
    @php
        $totalLogs = $logs->total();
        $todayLogs = \App\Models\AuditLog::whereDate('created_at', today())->count();
        $thisWeekLogs = \App\Models\AuditLog::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->count();
        $thisMonthLogs = \App\Models\AuditLog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $actionStats = [
            'create' => \App\Models\AuditLog::where('aksi', 'create')->count(),
            'update' => \App\Models\AuditLog::where('aksi', 'update')->count(),
            'delete' => \App\Models\AuditLog::where('aksi', 'delete')->count(),
            'login' => \App\Models\AuditLog::where('aksi', 'login')->count(),
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($totalLogs) }}</p>
            <p class="text-xs text-gray-500">Total Aktivitas</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($todayLogs) }}</p>
            <p class="text-xs text-gray-500">Hari Ini</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($thisWeekLogs) }}</p>
            <p class="text-xs text-gray-500">Minggu Ini</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($thisMonthLogs) }}</p>
            <p class="text-xs text-gray-500">Bulan Ini</p>
        </div>
    </div>

    {{-- Action Distribution --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-700">Distribusi Aktivitas</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                    <div
                        class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-emerald-700 truncate">Create</p>
                        <p class="text-lg font-extrabold text-gray-800">{{ number_format($actionStats['create']) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                    <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-blue-700 truncate">Update</p>
                        <p class="text-lg font-extrabold text-gray-800">{{ number_format($actionStats['update']) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-red-50 border border-red-100">
                    <div class="w-10 h-10 rounded-lg bg-red-500 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-red-700 truncate">Delete</p>
                        <p class="text-lg font-extrabold text-gray-800">{{ number_format($actionStats['delete']) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 border border-indigo-100">
                    <div
                        class="w-10 h-10 rounded-lg bg-indigo-500 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-indigo-700 truncate">Login</p>
                        <p class="text-lg font-extrabold text-gray-800">{{ number_format($actionStats['login']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <x-card class="mb-6">
        <div class="p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Filter Aktivitas</h3>
            </div>

            <form method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">User</label>
                        <select name="user_id" class="form-select-custom w-full text-sm">
                            <option value="">Semua User</option>
                            @foreach ($userOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('user_id') == $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Aksi</label>
                        <select name="aksi" class="form-select-custom w-full text-sm">
                            <option value="">Semua Aksi</option>
                            @foreach ($aksiOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('aksi') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Model</label>
                        <select name="model" class="form-select-custom w-full text-sm">
                            <option value="">Semua Model</option>
                            @foreach ($modelOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('model') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
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
                    @if (request('search') ||
                            request('aksi') ||
                            request('model') ||
                            request('user_id') ||
                            request('start_date') ||
                            request('end_date'))
                        <a href="{{ route('administrator.audit-log.index') }}"
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
        if (request('start_date')) {
            $activeFilters[] = ['label' => 'Dari', 'value' => request('start_date')];
        }
        if (request('end_date')) {
            $activeFilters[] = ['label' => 'Sampai', 'value' => request('end_date')];
        }
        if (request('user_id') && isset($userOptions[request('user_id')])) {
            $activeFilters[] = ['label' => 'User', 'value' => $userOptions[request('user_id')]];
        }
        if (request('aksi') && isset($aksiOptions[request('aksi')])) {
            $activeFilters[] = ['label' => 'Aksi', 'value' => $aksiOptions[request('aksi')]];
        }
        if (request('model') && isset($modelOptions[request('model')])) {
            $activeFilters[] = ['label' => 'Model', 'value' => $modelOptions[request('model')]];
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
                    {{ $filter['label'] }}: <span class="font-bold">{{ $filter['value'] }}</span>
                </span>
            @endforeach
            <a href="{{ route('administrator.audit-log.index') }}"
                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Hapus Semua
            </a>
        </div>
    @endif

    {{-- Timeline --}}
    @forelse ($logs as $log)
        @php
            $actionColor = match ($log->aksi) {
                'create' => 'emerald',
                'update' => 'blue',
                'delete' => 'red',
                'login' => 'indigo',
                'logout' => 'gray',
                'import' => 'amber',
                'password_reset' => 'amber',
                default => 'gray',
            };

            $actionIcon = match ($log->aksi) {
                'create' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
                'update'
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />',
                'delete'
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />',
                'login'
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />',
                'logout'
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />',
                'import'
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />',
                'password_reset'
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />',
                default
                    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
            };

            $dotBg = match ($actionColor) {
                'emerald' => 'bg-emerald-500',
                'blue' => 'bg-blue-500',
                'red' => 'bg-red-500',
                'indigo' => 'bg-indigo-500',
                'amber' => 'bg-amber-500',
                default => 'bg-gray-400',
            };

            $iconBg = match ($actionColor) {
                'emerald' => 'bg-emerald-50 text-emerald-600',
                'blue' => 'bg-blue-50 text-blue-600',
                'red' => 'bg-red-50 text-red-600',
                'indigo' => 'bg-indigo-50 text-indigo-600',
                'amber' => 'bg-amber-50 text-amber-600',
                default => 'bg-gray-100 text-gray-500',
            };

            $badgeBg = match ($actionColor) {
                'emerald' => 'bg-emerald-100 text-emerald-700',
                'blue' => 'bg-blue-100 text-blue-700',
                'red' => 'bg-red-100 text-red-700',
                'indigo' => 'bg-indigo-100 text-indigo-700',
                'amber' => 'bg-amber-100 text-amber-700',
                default => 'bg-gray-100 text-gray-600',
            };
        @endphp

        <div class="relative pl-10 pb-8 last:pb-0 group">
            {{-- Timeline line --}}
            <div class="absolute left-[17px] top-8 bottom-0 w-0.5 bg-gray-200 group-last:hidden"></div>

            {{-- Timeline dot --}}
            <div
                class="absolute left-2 top-1.5 w-[18px] h-[18px] rounded-full {{ $dotBg }} ring-4 ring-white shadow-sm flex items-center justify-center">
                <div class="w-2 h-2 rounded-full bg-white"></div>
            </div>

            {{-- Content card --}}
            <div
                class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                {{-- Card header --}}
                <div
                    class="flex items-start sm:items-center justify-between gap-3 px-4 py-3 bg-gray-50/60 border-b border-gray-100">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg {{ $iconBg }} flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">{!! $actionIcon !!}</svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-800">{{ $log->user_label }}</span>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badgeBg }}">
                                    {{ $log->aksi_label }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $log->created_at->diffForHumans() }} &middot; {{ $log->created_at_label }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('administrator.audit-log.show', $log) }}"
                        class="btn-action btn-action-view shrink-0" title="Lihat Detail">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </div>

                {{-- Card body --}}
                <div class="px-4 py-3">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-3.75" />
                            </svg>
                            <span class="text-gray-600">{{ $log->model_label }}</span>
                            @if ($log->model_id)
                                <span class="text-gray-400 text-xs">#{{ $log->model_id }}</span>
                            @endif
                        </div>
                        @if ($log->role_snapshot)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                <span class="text-gray-600">{{ $log->role_snapshot }}</span>
                            </div>
                        @endif
                        @if ($log->ip_address)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                                <span class="text-gray-500 text-xs font-mono">{{ $log->ip_address }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <x-card>
            <x-empty-state title="Belum Ada Audit" description="Belum ada aktivitas yang tercatat." icon="folder" />
        </x-card>
    @endforelse

    {{-- Pagination --}}
    @if ($logs->hasPages())
        <div class="mt-6">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</x-app-layout>
