@php
    $namaUsaha = trim((string) ($data['nama_usaha'] ?? $data['jenis_usaha'] ?? '-'));
    $catatanUsaha = $data['catatan_usaha'] ?? 'Surat Keterangan ini bukan Surat Izin Usaha (HO)';
@endphp

@include('surat.templates.partials._signer_identity')

<p class="no-indent">Menerangkan dengan sebenarnya bahwa :</p>

@include('surat.templates.partials._data_table', [
    'fields' => [
        'nama_lengkap',
        'tempat_tanggal_lahir',
        'nik',
        'no_kk',
        'kewarganegaraan',
        'agama',
        'status_perkawinan',
        'pekerjaan',
        'alamat',
        'alamat_domisili',
    ],
    'fieldLabels' => $fieldLabels,
    'data' => $data,
    'numbered' => false,
])

<p class="hanging">
    Benar nama tersebut di atas mempunyai Usaha : <strong>&ldquo; {{ strtoupper($namaUsaha) }} &rdquo;</strong>
</p>

@include('surat.templates.partials._data_table', [
    'fields' => ['jenis_usaha', 'alamat_usaha', 'ukuran_tempat_usaha', 'jumlah_tenaga_pembantu'],
    'fieldLabels' => array_merge($fieldLabels, [
        'jenis_usaha' => 'Jenis Usaha',
        'alamat_usaha' => 'Lokasi Usaha',
        'ukuran_tempat_usaha' => 'Ukuran Tempat Usaha',
        'jumlah_tenaga_pembantu' => 'Tenaga Pembantu',
    ]),
    'data' => $data,
    'numbered' => false,
])

<p>
    Demikian Surat Keterangan ini dibuat dengan sebenarnya dan diberikan kepada yang bersangkutan
    sebagai pegangan dan untuk dipergunakan sebagaimana mestinya.
</p>

<p class="no-indent">Catatan : - {{ $catatanUsaha }}</p>
