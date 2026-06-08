{{--
    Template Kategori: INTERNAL
    Untuk surat-surat internal desa seperti:
    - SBALASAN (Balasan)

    Struktur lebih bebas, berbasis body content
--}}
@extends('surat.templates._layout')

@section('content')
    {{-- PERIHAL & LAMPIRAN (untuk surat resmi) --}}
    @if (!empty($data['perihal']) || !empty($data['lampiran']))
        <table class="data-table" style="margin-bottom: 20px;">
            @if (!empty($data['lampiran']))
                <tr>
                    <td class="label" style="width: 15%;">Lampiran</td>
                    <td class="separator">:</td>
                    <td class="value">{{ $data['lampiran'] }}</td>
                </tr>
            @endif
            @if (!empty($data['perihal']))
                <tr>
                    <td class="label">Perihal</td>
                    <td class="separator">:</td>
                    <td class="value"><strong>{{ $data['perihal'] }}</strong></td>
                </tr>
            @endif
        </table>
    @endif

    {{-- BLOK DETAIL UNTUK SURAT BALASAN --}}
    @if ($jenisSurat->kode === 'SBALASAN')
        <table class="data-table" style="margin-bottom: 15px;">
            <tr>
                <td class="label" style="width: 20%;">Kepada Yth.</td>
                <td class="separator">:</td>
                <td class="value">{{ $data['kepada'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Tujuan</td>
                <td class="separator">:</td>
                <td class="value">{{ $data['alamat_tujuan'] ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Perihal/Subject</td>
                <td class="separator">:</td>
                <td class="value">{{ $data['perihal'] ?? 'Balasan surat' }}</td>
            </tr>
            @if (!empty($data['nomor_rujukan']))
                <tr>
                    <td class="label">Menjawab Surat</td>
                    <td class="separator">:</td>
                    <td class="value">Nomor {{ $data['nomor_rujukan'] }}</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- KEPADA (untuk undangan/surat keluar) --}}
    @if (!empty($data['kepada']) && $jenisSurat->kode !== 'SBALASAN')
        <p class="no-indent">
            Kepada Yth.<br>
            <strong>{{ $data['kepada'] }}</strong><br>
            @if (!empty($data['alamat_tujuan']))
                {{ $data['alamat_tujuan'] }}<br>
            @endif
            di Tempat
        </p>
        <br>
    @endif

    {{-- SALAM PEMBUKA --}}
    <p class="no-indent">{{ $sections['salam_pembuka'] ?? 'Dengan hormat,' }}</p>

    {{-- BODY / ISI SURAT --}}
    @if (!empty($sections['body']))
        <p>{{ $sections['body'] }}</p>
    @endif

    {{-- KONTEN KHUSUS UNDANGAN --}}
    @if ($jenisSurat->kode === 'SUNDGN' && !empty($data['agenda']))
        <p>Bersama ini kami mengundang Bapak/Ibu/Saudara untuk hadir pada:</p>
        @include('surat.templates.partials._data_table', [
            'fields' => ['hari_tanggal', 'waktu', 'tempat', 'acara'],
            'fieldLabels' => [
                'hari_tanggal' => 'Hari/Tanggal',
                'waktu' => 'Waktu',
                'tempat' => 'Tempat',
                'acara' => 'Acara',
            ],
            'data' => $data,
        ])
    @endif

    {{-- KONTEN KHUSUS SURAT TUGAS --}}
    @if ($jenisSurat->kode === 'SKTGS' && !empty($data['penerima_tugas']))
        <p class="no-indent">Memberikan tugas kepada:</p>
        @include('surat.templates.partials._data_table', [
            'fields' => ['nama_penerima', 'nip_penerima', 'jabatan_penerima'],
            'fieldLabels' => [
                'nama_penerima' => 'Nama',
                'nip_penerima' => 'NIP',
                'jabatan_penerima' => 'Jabatan',
            ],
            'data' => $data,
        ])
        <p class="no-indent mt-1">Untuk melaksanakan tugas:</p>
        @include('surat.templates.partials._data_table', [
            'fields' => ['uraian_tugas', 'tanggal_tugas', 'tempat_tugas'],
            'fieldLabels' => [
                'uraian_tugas' => 'Uraian Tugas',
                'tanggal_tugas' => 'Tanggal',
                'tempat_tugas' => 'Tempat',
            ],
            'data' => $data,
        ])
    @endif
@endsection

@section('closing')
    <p class="mt-2">{{ $sections['salam_penutup'] ?? 'Atas perhatian dan kehadirannya, kami ucapkan terima kasih.' }}</p>
@endsection
