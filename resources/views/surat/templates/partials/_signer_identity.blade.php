@php
    $kepalaDesa = array_merge([
        'nama' => config('app.desa.kepala_desa.nama', 'HENDRI SUSANTO'),
        'nik' => config('app.desa.kepala_desa.nik'),
        'jabatan' => config('app.desa.kepala_desa.jabatan', 'Kepala Desa'),
        'alamat' => config('app.desa.kepala_desa.alamat') ?: config('app.desa.alamat'),
    ], $data['kepala_desa'] ?? []);

    $signerData = [
        'nama_kepala_desa' => $kepalaDesa['nama'] ?? '-',
        'nik_kepala_desa' => $kepalaDesa['nik'] ?? '-',
        'jabatan_kepala_desa' => $kepalaDesa['jabatan'] ?? 'Kepala Desa',
        'alamat_kepala_desa' => $kepalaDesa['alamat'] ?? '-',
    ];

    $signerLabels = [
        'nama_kepala_desa' => 'Nama',
        'nik_kepala_desa' => 'NIK',
        'jabatan_kepala_desa' => 'Jabatan',
        'alamat_kepala_desa' => 'Alamat',
    ];
@endphp

<p class="no-indent">Yang bertanda tangan di bawah ini :</p>
@include('surat.templates.partials._data_table', [
    'fields' => ['nama_kepala_desa', 'nik_kepala_desa', 'jabatan_kepala_desa', 'alamat_kepala_desa'],
    'fieldLabels' => $signerLabels,
    'data' => $signerData,
    'numbered' => false,
])
