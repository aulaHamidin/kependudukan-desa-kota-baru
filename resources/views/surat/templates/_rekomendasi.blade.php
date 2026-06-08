{{--
    Template Kategori: REKOMENDASI
    Untuk surat-surat rekomendasi seperti:
    - SREKOM (Rekomendasi Umum), SKBS (Beasiswa)
    - SKAKTF (Aktif Organisasi), SKPNLTN (Penelitian), SKMAGANG (Magang/PKL)

    Struktur: Intro → Data Pemohon → Rekomendasi → Detail Kegiatan → Harapan
--}}
@extends('surat.templates._layout')

@section('content')
    {{-- INTRO --}}
    <p class="no-indent">
        {{ $sections['intro'] ?? 'Yang bertanda tangan di bawah ini, Kepala Desa, memberikan rekomendasi kepada:' }}</p>

    {{-- DATA PEMOHON --}}
    @include('surat.templates.partials._data_table', [
        'fields' => $sections['data_fields'] ?? ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
        'fieldLabels' => $fieldLabels,
        'data' => $data,
    ])

    {{-- BODY / REKOMENDASI --}}
    @if (!empty($sections['body']))
        <p>{{ $sections['body'] }}</p>
    @else
        <p>
            Adalah benar warga Desa kami yang kami kenal baik dan memiliki perilaku yang baik
            selama tinggal di wilayah Desa {{ $data['desa'] ?? 'kami' }}.
        </p>
    @endif

    {{-- DETAIL KEGIATAN (untuk beasiswa, penelitian, magang) --}}
    @if (!empty($sections['activity_fields']) && is_array($sections['activity_fields']))
        <p class="no-indent mt-1">{{ $sections['activity_intro'] ?? 'Dengan keterangan kegiatan sebagai berikut:' }}</p>
        @include('surat.templates.partials._data_table', [
            'fields' => $sections['activity_fields'],
            'fieldLabels' => array_merge($fieldLabels, [
                'nama_instansi' => 'Nama Instansi/Lembaga',
                'jenis_beasiswa' => 'Jenis Beasiswa',
                'judul_penelitian' => 'Judul Penelitian',
                'periode_magang' => 'Periode Magang',
                'nama_organisasi' => 'Nama Organisasi',
                'jabatan_organisasi' => 'Jabatan',
                'periode_aktif' => 'Periode Aktif',
            ]),
            'data' => $data,
        ])
    @endif

    {{-- TUJUAN --}}
    @if ($sections['show_purpose'] ?? true)
        <p>
            {{ $sections['purpose_label'] ?? 'Rekomendasi ini diberikan untuk keperluan' }}
            <strong>{{ $data['tujuan'] ?? '...................................................' }}</strong>.
        </p>
    @endif

    {{-- HARAPAN --}}
    <p>
        {{ $sections['closing'] ?? 'Kami berharap pihak yang berwenang dapat memberikan kesempatan dan pertimbangan kepada yang bersangkutan.' }}
    </p>
@endsection
