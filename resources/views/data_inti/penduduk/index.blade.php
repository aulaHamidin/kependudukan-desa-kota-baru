{{-- Data Inti - Penduduk Index --}}
<x-app-layout>
    <x-slot name="title">Data Penduduk</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Data Inti', 'url' => '#'], ['label' => 'Penduduk']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Data Penduduk" subtitle="Kelola seluruh data kependudukan desa.">
            <x-slot name="actions">
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    @if (old('_modal'))
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', '{{ old('_modal') }}'))"></div>
    @endif

    {{-- Stats Section --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card :value="$stats['total'] ?? 0" label="Total Penduduk" icon="users" color="primary" />
        <x-stat-card :value="$stats['aktif'] ?? 0" label="Total Penduduk Aktif" icon="check" color="success" />
        <x-stat-card :value="$stats['laki_laki'] ?? 0" label="Total Laki-Laki" icon="users" color="primary" />
        <x-stat-card :value="$stats['perempuan'] ?? 0" label="Total Perempuan" icon="users" color="rose" />
    </div>

    @include('data_inti.penduduk.partials.table', [
        'penduduks' => $penduduks,
        'statusOptions' => $statusOptions,
        'rtOptions' => $rtOptions,
    ])
    @include('data_inti.penduduk.partials.modals', [
        'penduduks' => $penduduks,
    ])
</x-app-layout>
