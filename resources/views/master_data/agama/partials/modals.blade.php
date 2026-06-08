@foreach ($items as $item)
    @php
        $editModal = 'edit-agama-' . $item->kode;
        $useOld = old('_modal') === $editModal;
    @endphp

    <x-modal.form :name="$editModal" title="Edit Agama" subtitle="Perbarui data agama {{ $item->nama }}.">
        <form id="{{ $editModal }}-form" method="POST" action="{{ route('master.agama.update', $item->kode) }}"
            class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="{{ $editModal }}">

            @include('master_data.agama.partials.form-fields', [
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

    <x-modal.confirm :name="'delete-agama-' . $item->kode" title="Hapus Agama?"
        description="Data agama {{ $item->nama }} akan dihapus atau dinonaktifkan jika sudah digunakan."
        :action="route('master.agama.destroy', $item->kode)" />
@endforeach

<x-modal.form name="create-agama" title="Tambah Agama" subtitle="Tambahkan data agama baru ke dalam sistem.">
    <form id="create-agama-form" method="POST" action="{{ route('master.agama.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="_modal" value="create-agama">

        @include('master_data.agama.partials.form-fields', [
            'item' => null,
            'useOld' => true,
        ])
    </form>

    <x-slot name="footer">
        <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-agama')">
            Batal
        </x-button>
        <x-button type="submit" icon="save" form="create-agama-form">
            Simpan Agama
        </x-button>
    </x-slot>
</x-modal.form>
