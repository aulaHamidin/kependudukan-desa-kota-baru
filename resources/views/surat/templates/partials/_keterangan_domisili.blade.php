@include('surat.templates.partials._signer_identity')

@php
    $wilayahDesa = $data['wilayah_desa_text']
        ?? 'Desa ' . ($data['desa_bersih'] ?? $data['desa'] ?? '-')
            . ', Kecamatan ' . ($data['kecamatan_bersih'] ?? $data['kecamatan'] ?? '-')
            . ', Kabupaten ' . ($data['kabupaten_bersih'] ?? $data['kabupaten'] ?? '-');
    $alamatDomisili = trim((string) ($data['alamat_domisili'] ?? $data['alamat'] ?? '-'));
    $alamatDomisili = rtrim($alamatDomisili, " \t\n\r\0\x0B.");
@endphp

<p class="no-indent">Menerangkan dengan sebenarnya bahwa :</p>

@include('surat.templates.partials._data_table', [
    'fields' => [
        'nama_lengkap',
        'bin_binti',
        'tempat_tanggal_lahir',
        'nik',
        'no_kk',
        'pekerjaan',
        'alamat',
        'alamat_domisili',
    ],
    'fieldLabels' => $fieldLabels,
    'data' => $data,
    'numbered' => false,
])

<p>
    Nama tersebut di atas adalah benar warga {{ $wilayahDesa }}, dan saat ini
    bertempat tinggal/berdomisili di {{ $alamatDomisili }}.
</p>

<p>
    Demikian Surat Keterangan Domisili ini dibuat dengan sebenarnya untuk dapat dipergunakan
    sebagaimana mestinya.
</p>
