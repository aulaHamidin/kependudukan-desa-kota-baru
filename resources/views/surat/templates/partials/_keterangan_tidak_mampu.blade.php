@php
    $wilayahDesa = $data['wilayah_desa_text'] ?? ($data['desa_info']['wilayah_desa_text'] ?? 'Desa');
    $program = $data['keperluan_program']
        ?? $data['tujuan']
        ?? 'KELENGKAPAN ADMINISTRASI PENERIMA BANTUAN PROGRAM INDONESIA PINTAR (PIP)';
    $childData = array_merge($data, [
        'no_kk_anak' => $data['no_kk_anak'] ?? $data['no_kk'] ?? null,
        'kewarganegaraan_anak' => $data['kewarganegaraan_anak'] ?? 'WNI',
        'alamat_anak' => $data['alamat_anak'] ?? $data['alamat'] ?? null,
        'alamat_domisili_anak' => $data['alamat_domisili_anak'] ?? $data['alamat_domisili'] ?? $data['alamat'] ?? null,
        'tempat_tanggal_lahir_anak' => trim(($data['tempat_lahir_anak'] ?? '-') . ', ' . (
            !empty($data['tanggal_lahir_anak'])
                ? \Carbon\Carbon::parse($data['tanggal_lahir_anak'])->locale('id')->translatedFormat('d F Y')
                : '-'
        )),
    ]);
@endphp

<p>
    Kepala {{ $wilayahDesa }}, menerangkan bahwa:
</p>

@include('surat.templates.partials._data_table', [
    'fields' => [
        'nama_lengkap',
        'bin_binti',
        'tempat_tanggal_lahir',
        'nik',
        'no_kk',
        'kewarganegaraan',
        'agama',
        'jenis_kelamin',
        'pekerjaan',
        'alamat',
        'alamat_domisili',
    ],
    'fieldLabels' => $fieldLabels,
    'data' => $data,
    'numbered' => false,
])

<p>Orang yang namanya tersebut di atas adalah benar Orang Tua/Wali dari :</p>

@include('surat.templates.partials._data_table', [
    'fields' => [
        'nama_anak',
        'bin_binti_anak',
        'tempat_tanggal_lahir_anak',
        'nik_anak',
        'no_kk_anak',
        'kewarganegaraan_anak',
        'agama_anak',
        'jenis_kelamin_anak',
        'pekerjaan_anak',
        'alamat_anak',
        'alamat_domisili_anak',
    ],
    'fieldLabels' => $fieldLabels,
    'data' => $childData,
    'numbered' => false,
])

<p>
    Berdasarkan keterangan dan keadaan yang sebenarnya, keluarga tersebut tergolong keluarga
    tidak mampu/kurang mampu. Surat keterangan ini dibuat untuk
    <strong>{{ strtoupper($program) }}</strong>.
</p>

<p>
    Demikian Surat Keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
</p>
