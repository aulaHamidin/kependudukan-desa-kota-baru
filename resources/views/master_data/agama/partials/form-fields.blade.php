@props(['item' => null, 'useOld' => false])

@php
    $kode = $item?->kode;
    $nama = $item?->nama;
    $urutan = $item?->urutan;
    $isActive = $item?->is_active;

    if ($useOld) {
        $kode = old('kode', $kode);
        $nama = old('nama', $nama);
        $urutan = old('urutan', $urutan);
        $isActive = old('is_active', $isActive ?? 1);
    }

    if ($isActive === null) {
        $isActive = true;
    }

    $statusValue = $isActive ? 1 : 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    @if ($item)
        <x-form-input name="kode" label="Kode" placeholder="Contoh: AG" :value="$kode" :useOld="$useOld" readonly
            disabled helper="Kode tidak dapat diubah" />
    @else
        <x-form-input name="kode" label="Kode" placeholder="Contoh: AG" required :value="$kode" :useOld="$useOld"
            helper="Maks. 2 karakter" />
    @endif

    <x-form-input name="nama" label="Nama" placeholder="Contoh: Islam" required :value="$nama" :useOld="$useOld"
        helper="Maks. 100 karakter" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="urutan" label="Urutan" type="number" min="1" placeholder="Contoh: 1" :value="$urutan"
        :useOld="$useOld" helper="Kosongkan untuk urutan otomatis" />

    <x-form-select name="is_active" label="Status" :options="[1 => 'Aktif', 0 => 'Nonaktif']" required :value="$statusValue" :useOld="$useOld" />
</div>
