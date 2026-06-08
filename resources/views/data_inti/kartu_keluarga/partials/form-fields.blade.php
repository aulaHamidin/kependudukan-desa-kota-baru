@php
    $useOld = $useOld ?? true;
    $item = $item ?? null;

    $noKk = $item?->no_kk;
    $alamat = $item?->alamat;
    $rtId = $item?->rt_id;
    $statusKk = $item?->status_kk ?? 'AKTIF';
    $tanggalTerbentuk = $item?->tanggal_terbentuk?->format('Y-m-d');

    if ($useOld) {
        $noKk = old('no_kk', $noKk);
        $alamat = old('alamat', $alamat);
        $rtId = old('rt_id', $rtId);
        $statusKk = old('status_kk', $statusKk);
        $tanggalTerbentuk = old('tanggal_terbentuk', $tanggalTerbentuk);
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form-nik name="no_kk" label="No. KK" required :value="$noKk" :useOld="$useOld" />
    <x-form-select name="status_kk" label="Status KK" :options="$statusKkOptions" required :value="$statusKk" :useOld="$useOld" />

    <x-form-date name="tanggal_terbentuk" label="Tanggal Terbentuk" required :value="$tanggalTerbentuk" :useOld="$useOld"
        max="today" />
    <x-form-select name="rt_id" label="RT" required :options="$rtOptions" :value="$rtId" :useOld="$useOld" />

    <div class="sm:col-span-2">
        <x-form-address name="alamat" label="Alamat" required :value="$alamat" />
    </div>
</div>
