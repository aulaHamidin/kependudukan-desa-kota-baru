@props(['rws', 'item' => null, 'useOld' => false])

@php
    $rwId = $item?->rw_id;
    $nomor = $item?->nomor_rt;
    $namaKetua = $item?->nama_ketua;
    $noHp = $item?->no_hp_ketua;

    if ($useOld) {
        $rwId = old('rw_id', $rwId);
        $nomor = old('nomor_rt', $nomor);
        $namaKetua = old('nama_ketua', $namaKetua);
        $noHp = old('no_hp_ketua', $noHp);
    }

    $rwOptions = $rws->mapWithKeys(function ($rw) {
        $label = 'RW' . str_pad($rw->nomor_rw ?? '', 3, '0', STR_PAD_LEFT);
        if ($rw->desa?->nama) {
            $label .= ' - ' . $rw->desa->nama;
        }
        return [$rw->id => $label];
    });
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-select name="rw_id" label="RW" required :options="$rwOptions" :value="$rwId" :useOld="$useOld" />

    <x-form-input name="nomor_rt" label="Nomor RT" placeholder="Contoh: 001" required :value="$nomor"
        :useOld="$useOld" />
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <x-form-input name="nama_ketua" label="Nama Ketua RT" placeholder="Contoh: Budi" :value="$namaKetua" :useOld="$useOld" />

    <x-form-input name="no_hp_ketua" label="No. HP Ketua" placeholder="Contoh: 08123456789" :value="$noHp"
        :useOld="$useOld" />
</div>
