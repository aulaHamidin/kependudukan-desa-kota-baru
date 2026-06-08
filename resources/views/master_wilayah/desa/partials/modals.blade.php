@foreach ($desa as $item)
    @php
        $editModal = 'edit-desa-' . $item->id;
        $useOld = old('_modal') === $editModal;
    @endphp

    @can('update', $item)
        <x-modal.form :name="$editModal" title="Edit Desa" subtitle="Perbarui data desa {{ $item->nama }}.">
            <form id="{{ $editModal }}-form" method="POST" action="{{ route('master.wilayah.desa.update', $item) }}"
                class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="{{ $editModal }}">

                @include('master_wilayah.desa.partials.form-fields', [
                    'prefix' => $editModal,
                    'item' => $item,
                    'useOld' => $useOld,
                ])
            </form>

            <x-slot name="footer">
                <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', '{{ $editModal }}')">
                    Batal
                </x-button>
                <x-button type="submit" icon="save" form="{{ $editModal }}-form">
                    Simpan Perubahan
                </x-button>
            </x-slot>
        </x-modal.form>
    @endcan

    @can('delete', $item)
        <x-modal.confirm :name="'delete-desa-' . $item->id" title="Hapus Desa?"
            description="Data desa {{ $item->nama }} akan dihapus. Pastikan tidak ada RW yang terkait."
            :action="route('master.wilayah.desa.destroy', $item)" />
    @endcan
@endforeach

@can('create', \App\Models\Desa::class)
    <x-modal.form name="create-desa" title="Tambah Desa" subtitle="Tambahkan data desa baru ke dalam sistem.">
        <form id="create-desa-form" method="POST" action="{{ route('master.wilayah.desa.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_modal" value="create-desa">

            @include('master_wilayah.desa.partials.form-fields', [
                'prefix' => 'create-desa',
                'item' => null,
                'useOld' => true,
            ])
        </form>

        <x-slot name="footer">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-desa')">
                Batal
            </x-button>
            <x-button type="submit" icon="save" form="create-desa-form">
                Simpan Desa
            </x-button>
        </x-slot>
    </x-modal.form>
@endcan
