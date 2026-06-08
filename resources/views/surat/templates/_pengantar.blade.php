{{--
    Template Kategori: PENGANTAR
    Untuk surat-surat pengantar ke instansi lain seperti:
    - SKLHR (Kelahiran), SKMT (Kematian), SKN (Nikah)
    - SKPD (Pindah/Datang)

    Struktur: Intro → Data Penduduk → Keterangan Khusus → Tujuan Instansi
--}}
@extends('surat.templates._layout')

@section('content')
    {{-- INTRO --}}
    <p class="no-indent">
        {{ $sections['intro'] ?? 'Yang bertanda tangan di bawah ini, Kepala Desa, dengan ini memberikan pengantar bahwa:' }}
    </p>

    {{-- DATA PENDUDUK --}}
    @include('surat.templates.partials._data_table', [
        'fields' => $sections['data_fields'] ?? ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
        'fieldLabels' => $fieldLabels,
        'data' => $data,
    ])

    {{-- BODY / KETERANGAN KHUSUS --}}
    @if (!empty($sections['body']))
        <p>{{ $sections['body'] }}</p>
    @else
        <p>
            Adalah benar warga Desa kami yang bermaksud untuk mengurus keperluan administrasi
            di instansi terkait.
        </p>
    @endif

    {{-- DATA TAMBAHAN (jika ada) --}}
    @if (!empty($sections['additional_fields']) && is_array($sections['additional_fields']))
        <p class="no-indent mt-1">Dengan keterangan tambahan sebagai berikut:</p>
        @include('surat.templates.partials._data_table', [
            'fields' => $sections['additional_fields'],
            'fieldLabels' => $fieldLabels,
            'data' => $data,
        ])
    @endif

    {{-- TUJUAN INSTANSI --}}
    @if ($sections['show_purpose'] ?? true)
        <p>
            {{ $sections['purpose_label'] ?? 'Surat pengantar ini ditujukan kepada' }}
            <strong>{{ $data['instansi_tujuan'] ?? ($data['tujuan'] ?? 'instansi terkait') }}</strong>
            untuk keperluan {{ $data['keperluan'] ?? 'pengurusan administrasi' }}.
        </p>
    @endif
@endsection

@section('closing')
    <p class="mt-2">
        Demikian surat pengantar ini dibuat, atas perhatian dan kerjasamanya diucapkan terima kasih.
    </p>
@endsection
