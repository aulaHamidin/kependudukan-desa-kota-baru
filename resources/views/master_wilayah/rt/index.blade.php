<x-app-layout>
    <x-slot name="title">Data RT</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Wilayah'], ['label' => 'RT']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Data RT" subtitle="Kelola data Rukun Tetangga (RT) dalam sistem">
        </x-page-header>
    </x-slot>

    <x-alert />

    @if (old('_modal'))
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', '{{ old('_modal') }}'))"></div>
    @endif

    @include('master_wilayah.rt.partials.table', ['rts' => $rts, 'rws' => $rws])
    @include('master_wilayah.rt.partials.drawers', ['rts' => $rts])
    @include('master_wilayah.rt.partials.modals', ['rts' => $rts, 'rws' => $rws])

    @push('scripts')
        <script>
            function rtTableFilter() {
                return {
                    filters: {
                        rw: ''
                    },
                    dt: null,
                    initialized: false,
                    init() {
                        if (this.initialized) return;
                        this.initialized = true;

                        const store = this.$store?.datatables;
                        if (!store) return;

                        const instance = store.get('rtTable');
                        if (instance) {
                            this.dt = instance;
                            return;
                        }

                        store.onReady('rtTable', (dt) => {
                            this.dt = dt;
                        });
                    },
                    applyFilters() {
                        if (!this.dt) return;

                        const queries = [];

                        if (this.filters.rw) {
                            queries.push({
                                terms: [this.filters.rw],
                                columns: [1]
                            });
                        }

                        this.dt.search('', undefined, 'filters');

                        if (queries.length > 0) {
                            this.dt.multiSearch(queries, 'filters');
                        }
                    },
                    resetFilters() {
                        this.filters.rw = '';

                        if (this.dt) {
                            this.dt.search('', undefined, 'filters');
                        }
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
