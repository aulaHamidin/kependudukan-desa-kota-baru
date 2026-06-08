{{--
    Template Kategori: IZIN
    Untuk surat-surat izin seperti:
    - SIK (Izin Keramaian), SIBNG (Izin Mendirikan Bangunan)

    Struktur: Intro → Data Pemohon → Detail Kegiatan/Bangunan → Syarat & Ketentuan
--}}
@extends('surat.templates._layout')

@section('content')
    {{-- INTRO --}}
    <p class="no-indent">
        {{ $sections['intro'] ?? 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan izin kepada:' }}</p>

    {{-- DATA PEMOHON --}}
    @include('surat.templates.partials._data_table', [
        'fields' => $sections['data_fields'] ?? ['nama_lengkap', 'nik', 'alamat'],
        'fieldLabels' => $fieldLabels,
        'data' => $data,
    ])

    {{-- BODY / DETAIL KEGIATAN --}}
    @if (!empty($sections['body']))
        <p>{{ $sections['body'] }}</p>
    @else
        <p>
            Untuk mengadakan kegiatan sebagaimana tersebut di bawah ini:
        </p>
    @endif

    {{-- DETAIL KEGIATAN/BANGUNAN --}}
    @if (!empty($sections['detail_fields']) && is_array($sections['detail_fields']))
        @include('surat.templates.partials._data_table', [
            'fields' => $sections['detail_fields'],
            'fieldLabels' => array_merge($fieldLabels, [
                'jenis_kegiatan' => 'Jenis Kegiatan',
                'tanggal_mulai' => 'Tanggal Mulai',
                'tanggal_selesai' => 'Tanggal Selesai',
                'waktu' => 'Waktu',
                'tempat' => 'Tempat/Lokasi',
                'jumlah_undangan' => 'Perkiraan Undangan',
                'jenis_bangunan' => 'Jenis Bangunan',
                'luas_bangunan' => 'Luas Bangunan',
                'lokasi_bangunan' => 'Lokasi Bangunan',
            ]),
            'data' => $data,
        ])
    @endif

    {{-- SYARAT & KETENTUAN --}}
    @if (!empty($sections['terms']) && is_array($sections['terms']))
        <p class="no-indent mt-1">Dengan ketentuan sebagai berikut:</p>
        <ol style="margin-left: 20px;">
            @foreach ($sections['terms'] as $term)
                <li>{{ $term }}</li>
            @endforeach
        </ol>
    @else
        <p class="no-indent mt-1">Dengan ketentuan sebagai berikut:</p>
        <ol style="margin-left: 20px;">
            <li>Wajib menjaga ketertiban dan keamanan selama kegiatan berlangsung.</li>
            <li>Tidak mengganggu ketertiban umum dan masyarakat sekitar.</li>
            <li>Mematuhi peraturan perundang-undangan yang berlaku.</li>
            <li>Izin ini dapat dicabut apabila terjadi pelanggaran.</li>
        </ol>
    @endif
@endsection
