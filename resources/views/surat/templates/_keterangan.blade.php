{{--
    Template Kategori: KETERANGAN
    Untuk surat-surat keterangan umum seperti:
    - SKD (Domisili), SKTM (Tidak Mampu), SKBB (Berkelakuan Baik)
    - SKU (Usaha), SKHBS (Penghasilan), SKBK (Belum Kawin)
    - SKPEND (Penduduk Sementara), dll

    Struktur: Intro → Data Penduduk → Body → Tujuan → Penutup
--}}
@extends('surat.templates._layout')

@section('content')
    {{-- INTRO --}}
    <p class="no-indent">{{ $sections['intro'] ?? 'Yang bertanda tangan di bawah ini, Kepala Desa, menerangkan bahwa:' }}</p>

    {{-- DATA PENDUDUK --}}
    @include('surat.templates.partials._data_table', [
        'fields' => $sections['data_fields'] ?? ['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'alamat'],
        'fieldLabels' => $fieldLabels,
        'data' => $data,
    ])

    {{-- BODY --}}
    @if (!empty($sections['body']))
        <p>{{ $sections['body'] }}</p>
    @else
        <p>
            Adalah benar warga Desa {{ $data['desa'] ?? '-' }} yang berdomisili di alamat tersebut di atas.
        </p>
    @endif

    {{-- TUJUAN/KEPERLUAN --}}
    @if ($sections['show_purpose'] ?? true)
        <p>
            {{ $sections['purpose_label'] ?? 'Surat ini dibuat untuk keperluan' }}
            <strong>{{ $data['tujuan'] ?? '...................................................' }}</strong>.
        </p>
    @endif
@endsection
