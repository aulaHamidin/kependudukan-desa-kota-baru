<x-app-layout>
    <x-slot name="title">Data RW</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Wilayah'], ['label' => 'RW']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Data RW" subtitle="Kelola data Rukun Warga (RW) dalam sistem">
        </x-page-header>
    </x-slot>

    <x-alert />

    @if (old('_modal'))
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', '{{ old('_modal') }}'))"></div>
    @endif

    @include('master_wilayah.rw.partials.table', ['rws' => $rws, 'desas' => $desas])
    @include('master_wilayah.rw.partials.drawers', ['rws' => $rws])
    @include('master_wilayah.rw.partials.modals', ['rws' => $rws, 'desas' => $desas])

    @push('scripts')
        <script>
            function rwTableFilter() {
                return {
                    filters: {
                        desa: ''
                    },
                    dt: null,
                    initialized: false,
                    init() {
                        if (this.initialized) return;
                        this.initialized = true;

                        const store = this.$store?.datatables;
                        if (!store) return;

                        const instance = store.get('rwTable');
                        if (instance) {
                            this.dt = instance;
                            return;
                        }

                        store.onReady('rwTable', (dt) => {
                            this.dt = dt;
                        });
                    },
                    applyFilters() {
                        if (!this.dt) return;

                        const queries = [];

                        if (this.filters.desa) {
                            queries.push({
                                terms: [this.filters.desa],
                                columns: [0]
                            });
                        }

                        this.dt.search('', undefined, 'filters');

                        if (queries.length > 0) {
                            this.dt.multiSearch(queries, 'filters');
                        }
                    },
                    resetFilters() {
                        this.filters.desa = '';

                        if (this.dt) {
                            this.dt.search('', undefined, 'filters');
                        }
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
