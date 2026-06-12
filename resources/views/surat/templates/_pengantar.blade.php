{{--
    Template Kategori: PENGANTAR
    Untuk surat-surat pengantar ke instansi lain seperti:
    - SKLHR (Kelahiran), SKMT (Kematian), SKN (Nikah)
    - SKPD (Pindah/Datang)

    Struktur: Intro → Data Penduduk → Keterangan Khusus → Tujuan Instansi
--}}
@extends('surat.templates._layout')

@section('content')
    @php
        $keperluan = trim((string) ($data['keperluan'] ?? ($data['tujuan'] ?? 'pengurusan administrasi')));
        $keperluan = preg_replace('/^\s*(untuk\s+keperluan|keperluan)\s+/i', '', $keperluan);
        $targetInstansi = $data['instansi_tujuan'] ?? ($sections['target_instansi'] ?? null);
        $purposeLabel = rtrim((string) ($sections['purpose_label'] ?? 'Surat ini dibuat untuk keperluan'), " .");
    @endphp

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
        @if ($targetInstansi)
            <p>
                Surat ini ditujukan kepada <strong>{{ $targetInstansi }}</strong>
                untuk keperluan <strong>{{ $keperluan }}</strong>.
            </p>
        @else
            <p>
                {{ $purposeLabel }}
                <strong>{{ $keperluan }}</strong>.
            </p>
        @endif
    @endif
@endsection

@section('closing')
    <p class="mt-2">
        {{ $sections['closing'] ?? 'Demikian surat ini dibuat, atas perhatian dan kerja samanya diucapkan terima kasih.' }}
    </p>
@endsection
