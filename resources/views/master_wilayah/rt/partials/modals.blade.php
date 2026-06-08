@foreach ($rts as $item)
    @php
        $editModal = 'edit-rt-' . $item->id;
        $useOld = old('_modal') === $editModal;
    @endphp

    @can('update', $item)
        <x-modal.form :name="$editModal" title="Edit RT"
            subtitle="Perbarui data RT{{ str_pad($item->nomor_rt, 3, '0', STR_PAD_LEFT) }}.">
            <form id="{{ $editModal }}-form" method="POST" action="{{ route('master.wilayah.rt.update', $item) }}"
                class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="{{ $editModal }}">

                @include('master_wilayah.rt.partials.form-fields', [
                    'rws' => $rws,
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
        <x-modal.confirm :name="'delete-rt-' . $item->id" title="Hapus RT?"
            description="Data RT{{ str_pad($item->nomor_rt, 3, '0', STR_PAD_LEFT) }} akan dihapus. Pastikan tidak ada penduduk yang terkait."
            :action="route('master.wilayah.rt.destroy', $item)" />
    @endcan
@endforeach

@can('create', [\App\Models\Rt::class, auth()->user()->rw_id ?? 0])
    <x-modal.form name="create-rt" title="Tambah RT" subtitle="Tambahkan data RT baru ke dalam sistem.">
        <form id="create-rt-form" method="POST" action="{{ route('master.wilayah.rt.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="_modal" value="create-rt">

            @include('master_wilayah.rt.partials.form-fields', [
                'rws' => $rws,
                'item' => null,
                'useOld' => true,
            ])
        </form>

        <x-slot name="footer">
            <x-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'create-rt')">
                Batal
            </x-button>
            <x-button type="submit" icon="save" form="create-rt-form">
                Simpan RT
            </x-button>
        </x-slot>
    </x-modal.form>
@endcan
