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
    @php
        $keperluan = trim((string) ($data['tujuan'] ?? '...................................................'));
        $keperluan = preg_replace('/^\s*(untuk\s+keperluan|keperluan)\s+/i', '', $keperluan);
        $purposeLabel = rtrim((string) ($sections['purpose_label'] ?? 'Surat ini dibuat untuk keperluan'), " .");
    @endphp

    @if ($jenisSurat->kode === 'SKD')
        @include('surat.templates.partials._keterangan_domisili')
    @elseif ($jenisSurat->kode === 'SKTM')
        @include('surat.templates.partials._keterangan_tidak_mampu')
    @elseif ($jenisSurat->kode === 'SKU')
        @include('surat.templates.partials._keterangan_usaha')
    @else
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
            {{ $purposeLabel }}
            <strong>{{ $keperluan }}</strong>.
        </p>
    @endif
    @endif
@endsection
