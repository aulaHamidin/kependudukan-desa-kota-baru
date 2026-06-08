@props(['prefix', 'item' => null, 'useOld' => false])

@php
    $kode = $item?->kode_desa;
    $nama = $item?->nama;
    $kecamatan = $item?->kecamatan;
    $kabupaten = $item?->kabupaten;
    $provinsi = $item?->provinsi;
    $kodePos = $item?->kode_pos;

    if ($useOld) {
        $kode = old('kode_desa', $kode);
        $nama = old('nama', $nama);
        $kecamatan = old('kecamatan', $kecamatan);
        $kabupaten = old('kabupaten', $kabupaten);
        $provinsi = old('provinsi', $provinsi);
        $kodePos = old('kode_pos', $kodePos);
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="kode_desa" label="Kode Desa" placeholder="Contoh: 3507010001" required :value="$kode"
        :useOld="$useOld" helper="Kode unik untuk identifikasi desa (maks. 20 karakter)" />

    <x-form-input name="nama" label="Nama Desa" placeholder="Contoh: Sukamaju" required :value="$nama"
        :useOld="$useOld" helper="Nama resmi desa (maks. 100 karakter)" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="kecamatan" label="Kecamatan" placeholder="Contoh: Kepanjen" required :value="$kecamatan"
        :useOld="$useOld" />

    <x-form-input name="kabupaten" label="Kabupaten/Kota" placeholder="Contoh: Malang" required :value="$kabupaten"
        :useOld="$useOld" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="provinsi" label="Provinsi" placeholder="Contoh: Jawa Timur" required :value="$provinsi"
        :useOld="$useOld" />

    <x-form-input name="kode_pos" label="Kode Pos" placeholder="Contoh: 65163" required :value="$kodePos"
        :useOld="$useOld" helper="Kode pos desa (maks. 10 karakter)" />
</div>
