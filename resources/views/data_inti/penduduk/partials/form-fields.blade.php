@php
    $useOld = $useOld ?? true;
    $item = $item ?? null;
    $includeStatusFields = $includeStatusFields ?? true;

    $nik = $item?->nik;
    $nama = $item?->nama_lengkap;
    $jenisKelamin = $item?->jenis_kelamin;
    $tempatLahir = $item?->tempat_lahir;
    $tglLahir = $item?->tgl_lahir?->format('Y-m-d');
    $statusPerkawinan = $item?->status_perkawinan;
    $agamaId = $item?->agama_id;
    $pendidikanId = $item?->pendidikan_id;
    $pekerjaanId = $item?->pekerjaan_id;
    $pendapatanId = $item?->pendapatan_range_id;
    $golonganDarahId = $item?->golongan_darah_id;
    $kewarganegaraan = $item?->kewarganegaraan ?? 'WNI';
    $noPaspor = $item?->no_paspor;
    $noHp = $item?->no_hp;
    $email = $item?->email;
    $rtId = $item?->rt_id;
    $statusCode = $item?->status_kependudukan_code;
    $tanggalStatus = $item?->tanggal_status?->format('Y-m-d');

    if ($useOld) {
        $nik = old('nik', $nik);
        $nama = old('nama_lengkap', $nama);
        $jenisKelamin = old('jenis_kelamin', $jenisKelamin);
        $tempatLahir = old('tempat_lahir', $tempatLahir);
        $tglLahir = old('tgl_lahir', $tglLahir);
        $statusPerkawinan = old('status_perkawinan', $statusPerkawinan);
        $agamaId = old('agama_id', $agamaId);
        $pendidikanId = old('pendidikan_id', $pendidikanId);
        $pekerjaanId = old('pekerjaan_id', $pekerjaanId);
        $pendapatanId = old('pendapatan_range_id', $pendapatanId);
        $golonganDarahId = old('golongan_darah_id', $golonganDarahId);
        $kewarganegaraan = old('kewarganegaraan', $kewarganegaraan);
        $noPaspor = old('no_paspor', $noPaspor);
        $noHp = old('no_hp', $noHp);
        $email = old('email', $email);
        $rtId = old('rt_id', $rtId);
        $statusCode = old('status_kependudukan_code', $statusCode);
        $tanggalStatus = old('tanggal_status', $tanggalStatus);
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-form-nik name="nik" required :value="$nik" :useOld="$useOld" />
    <x-form-input name="nama_lengkap" label="Nama Lengkap" required :value="$nama" :useOld="$useOld" />

    <x-form-select name="jenis_kelamin" label="Jenis Kelamin" required :options="['L' => 'Laki-laki', 'P' => 'Perempuan']" :value="$jenisKelamin"
        :useOld="$useOld" />
    <x-form-input name="tempat_lahir" label="Tempat Lahir" required :value="$tempatLahir" :useOld="$useOld" />

    <x-form-date name="tgl_lahir" label="Tanggal Lahir" required :value="$tglLahir" :useOld="$useOld" />
    <x-form-select name="status_perkawinan" label="Status Perkawinan" required :options="$statusPerkawinanOptions" :value="$statusPerkawinan"
        :useOld="$useOld" />

    <x-form-select name="agama_id" label="Agama" required :options="$agamaOptions" :value="$agamaId" :useOld="$useOld" />
    <x-form-select name="pendidikan_id" label="Pendidikan" required :options="$pendidikanOptions" :value="$pendidikanId"
        :useOld="$useOld" />

    <x-form-select name="pekerjaan_id" label="Pekerjaan" required :options="$pekerjaanOptions" :value="$pekerjaanId"
        :useOld="$useOld" />
    <x-form-select name="pendapatan_range_id" label="Range Pendapatan" :options="$pendapatanOptions" :value="$pendapatanId"
        :useOld="$useOld" />

    <x-form-select name="golongan_darah_id" label="Golongan Darah" :options="$golonganDarahOptions" :value="$golonganDarahId"
        :useOld="$useOld" />
    <x-form-select name="kewarganegaraan" label="Kewarganegaraan" required :options="$kewarganegaraanOptions" :value="$kewarganegaraan"
        :useOld="$useOld" />

    <x-form-input name="no_paspor" label="No. Paspor" :value="$noPaspor" :useOld="$useOld" />
    <x-form-input name="no_hp" label="No. HP" :value="$noHp" :useOld="$useOld" />

    <x-form-input name="email" label="Email" type="email" :value="$email" :useOld="$useOld" />
    <x-form-select name="rt_id" label="Alamat" required :options="$rtOptions" :value="$rtId" :useOld="$useOld" />

    @if ($includeStatusFields)
        <x-form-status-select label="Status Kependudukan" :options="$statusOptions" required :value="$statusCode"
            :useOld="$useOld" />
        <x-form-date name="tanggal_status" label="Tanggal Status" required :value="$tanggalStatus" :useOld="$useOld" />
    @endif
</div>
