{{-- Data Inti - Kartu Keluarga Index --}}
<x-app-layout>
    <x-slot name="title">Data Kartu Keluarga</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Data Inti', 'url' => '#'], ['label' => 'Kartu Keluarga']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Data Kartu Keluarga" subtitle="Kelola data kartu keluarga.">
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
        <x-stat-card :value="$stats['total']" label="Total KK" icon="home" color="primary" />
        <x-stat-card :value="$stats['aktif']" label="KK Aktif" icon="check" color="success" />
        <x-stat-card :value="$stats['non_aktif']" label="KK Non-Aktif" icon="x" color="danger" />
        <x-stat-card :value="$stats['no_kepala']" label="Tanpa Kepala KK" icon="alert-triangle" color="warning" />
    </div>

    @include('data_inti.kartu_keluarga.partials.table', [
        'kartuKeluargas' => $kartuKeluargas,
        'rtOptions' => $rtOptions,
        'statusKkOptions' => $statusKkOptions,
    ])
    @include('data_inti.kartu_keluarga.partials.modals', [
        'kartuKeluargas' => $kartuKeluargas,
        'rtOptions' => $rtOptions,
        'hubunganOptions' => $hubunganOptions,
        'statusKkOptions' => $statusKkOptions,
    ])
</x-app-layout>
