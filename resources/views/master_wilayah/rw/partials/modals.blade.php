@foreach ($rws as $item)
    @php
        $editModal = 'edit-rw-' . $item->id;
        $useOld = old('_modal') === $editModal;
    @endphp

    @can('update', $item)
        <x-modal.form :name="$editModal" title="Edit RW"
            subtitle="Perbarui data RW{{ str_pad($item->nomor_rw, 3, '0', STR_PAD_LEFT) }}.">
            <form id="{{ $editModal }}-form" method="POST" action="{{ route('master.wilayah.rw.update', $item) }}"
                class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="{{ $editModal }}">

                @include('master_wilayah.rw.partials.form-fields', [
                    'desas' => $desas,
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
        <x-modal.confirm :name="'delete-rw-' . $item->id" title="Hapus RW?"
            description="Data RW{{ str_pad($item->nomor_rw, 3, '0', STR_PAD_LEFT) }} akan dihapus. Pastikan tidak ada RT yang terkait."
            :action="route('master.wilayah.rw.destroy', $item)" />
    @endcan
@endforeach

@can('create', [\App\Models\Rw::class, auth()->user()->desa_id ?? 0])
    <x-modal.form name="create-rw" title="Tambah RW" subtitle="Tambahkan data RW baru ke dalam sistem.">
        <form id="create-rw-form" method="POST" action="{{ route('master.wilayah.rw.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_modal" value="create-rw">

            @include('master_wilayah.rw.partials.form-fields', [
                'desas' => $desas,
                'item' => null,
                'useOld' => true,
            ])
        </form>

        <x-slot name="footer">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-rw')">
                Batal
            </x-button>
            <x-button type="submit" icon="save" form="create-rw-form">
                Simpan RW
            </x-button>
        </x-slot>
    </x-modal.form>
@endcan
