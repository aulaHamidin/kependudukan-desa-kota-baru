@props(['desas', 'item' => null, 'useOld' => false])

@php
    $desaId = $item?->desa_id;
    $nomor = $item?->nomor_rw;
    $namaKetua = $item?->nama_ketua;
    $noHp = $item?->no_hp_ketua;

    if ($useOld) {
        $desaId = old('desa_id', $desaId);
        $nomor = old('nomor_rw', $nomor);
        $namaKetua = old('nama_ketua', $namaKetua);
        $noHp = old('no_hp_ketua', $noHp);
    }

    $desaOptions = $desas->pluck('nama', 'id');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-select name="desa_id" label="Desa" required :options="$desaOptions" :value="$desaId" :useOld="$useOld" />

    <x-form-input name="nomor_rw" label="Nomor RW" placeholder="Contoh: 001" required :value="$nomor"
        :useOld="$useOld" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="nama_ketua" label="Nama Ketua RW" placeholder="Contoh: Budi" :value="$namaKetua" :useOld="$useOld" />

    <x-form-input name="no_hp_ketua" label="No. HP Ketua" placeholder="Contoh: 08123456789" :value="$noHp"
        :useOld="$useOld" />
</div>
