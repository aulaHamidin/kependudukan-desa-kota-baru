{{--
    Template Kategori: PERNYATAAN
    Untuk surat-surat pernyataan/keterangan status seperti:
    - SKJD (Janda/Duda), SKCR (Cerai), SKWRST (Ahli Waris)

    Struktur: Intro → Data Pembuat Pernyataan → Pernyataan → Data Terkait → Penutup
--}}
@extends('surat.templates._layout')

@section('content')
    {{-- INTRO --}}
    <p class="no-indent">
        {{ $sections['intro'] ?? 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan dengan sebenarnya bahwa:' }}
    </p>

    {{-- DATA UTAMA --}}
    @include('surat.templates.partials._data_table', [
        'fields' => $sections['data_fields'] ?? ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
        'fieldLabels' => $fieldLabels,
        'data' => $data,
    ])

    {{-- BODY / PERNYATAAN --}}
    @if (!empty($sections['body']))
        <p>{{ $sections['body'] }}</p>
    @else
        <p>
            Berdasarkan data yang ada dan keterangan dari yang bersangkutan serta saksi-saksi,
            kami menyatakan bahwa keterangan di atas adalah benar adanya.
        </p>
    @endif

    {{-- DATA TERKAIT (pasangan, almarhum, ahli waris, dll) --}}
    @if (!empty($sections['related_data_intro']))
        <p class="no-indent mt-1">{{ $sections['related_data_intro'] }}</p>
    @endif

    @if (!empty($sections['related_fields']) && is_array($sections['related_fields']))
        @include('surat.templates.partials._data_table', [
            'fields' => $sections['related_fields'],
            'fieldLabels' => array_merge($fieldLabels, [
                'nama_pasangan' => 'Nama Pasangan',
                'tanggal_meninggal' => 'Tanggal Meninggal',
                'tanggal_cerai' => 'Tanggal Perceraian',
                'nomor_akta_cerai' => 'Nomor Akta Cerai',
                'nama_almarhum' => 'Nama Almarhum/Almarhumah',
            ]),
            'data' => $data,
        ])
    @endif

    {{-- DAFTAR AHLI WARIS (khusus SKWRST) --}}
    @if (!empty($data['ahli_waris']) && is_array($data['ahli_waris']))
        <p class="no-indent mt-1">Adapun yang menjadi ahli waris adalah:</p>
        <table class="data-table" style="margin-left: 20px;">
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #000;">No</th>
                <th style="text-align: left; border-bottom: 1px solid #000;">Nama</th>
                <th style="text-align: left; border-bottom: 1px solid #000;">Hubungan</th>
            </tr>
            @foreach ($data['ahli_waris'] as $index => $waris)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $waris['nama'] ?? '-' }}</td>
                    <td>{{ $waris['hubungan'] ?? '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- TUJUAN --}}
    @if ($sections['show_purpose'] ?? true)
        <p>
            {{ $sections['purpose_label'] ?? 'Surat pernyataan ini dibuat untuk keperluan' }}
            <strong>{{ $data['tujuan'] ?? '...................................................' }}</strong>.
        </p>
    @endif
@endsection
