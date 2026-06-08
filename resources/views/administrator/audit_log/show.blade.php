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

    $badgeBg = match ($actionColor) {
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'red' => 'bg-red-100 text-red-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'amber' => 'bg-amber-100 text-amber-700',
        default => 'bg-gray-100 text-gray-600',
    };

    $headerGradient = match ($actionColor) {
        'emerald' => 'from-emerald-500 to-emerald-700',
        'blue' => 'from-blue-500 to-blue-700',
        'red' => 'from-red-500 to-red-700',
        'indigo' => 'from-indigo-500 to-indigo-700',
        'amber' => 'from-amber-500 to-amber-700',
        default => 'from-gray-500 to-gray-700',
    };
@endphp
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Administrator', 'url' => '#'],
            ['label' => 'Audit Log', 'url' => route('administrator.audit-log.index')],
            ['label' => 'Detail'],
        ]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Detail Audit Log" subtitle="Rincian aktivitas sistem.">
            <x-slot name="actions">
                <x-button variant="secondary" :href="route('administrator.audit-log.index')">
                    Kembali
                </x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    <div class="space-y-6">
        {{-- Header Banner --}}
        <div class="bg-gradient-to-br {{ $headerGradient }} rounded-xl p-5 sm:p-6 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-lg font-bold">{{ $log->user_label }}</span>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur-sm">
                            {{ $log->aksi_label }}
                        </span>
                    </div>
                    <p class="text-white/70 text-sm">
                        {{ $log->created_at->diffForHumans() }} &middot; {{ $log->created_at_label }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    @if ($log->model_label)
                        <div class="text-right">
                            <div class="text-xs text-white/60">Model</div>
                            <div class="font-semibold">{{ $log->model_label }}
                                @if ($log->model_id)
                                    <span class="text-white/60">#{{ $log->model_id }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">User</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $log->user_label }}</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">Role</div>
                        <div class="text-sm font-semibold text-gray-800">{{ $log->role_snapshot ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">IP Address</div>
                        <div class="text-sm font-semibold text-gray-800 font-mono">{{ $log->ip_address ?? '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-medium text-gray-400 uppercase tracking-wide">User Agent</div>
                        <div class="text-sm text-gray-800 truncate" title="{{ $log->user_agent ?? '-' }}">
                            {{ Str::limit($log->user_agent ?? '-', 30) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Perubahan --}}
        @if ($log->old_values || $log->new_values)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                        Detail Perubahan
                    </h3>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    {{-- Old Values --}}
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nilai Lama</span>
                        </div>
                        <pre
                            class="text-xs bg-red-50/50 text-gray-800 rounded-lg p-4 overflow-auto border border-red-100 max-h-96 font-mono leading-relaxed">{{ $log->old_values_pretty }}</pre>
                    </div>
                    {{-- New Values --}}
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nilai Baru</span>
                        </div>
                        <pre
                            class="text-xs bg-emerald-50/50 text-gray-800 rounded-lg p-4 overflow-auto border border-emerald-100 max-h-96 font-mono leading-relaxed">{{ $log->new_values_pretty }}</pre>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
