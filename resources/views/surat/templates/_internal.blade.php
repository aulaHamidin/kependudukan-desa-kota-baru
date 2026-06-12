{{--
    Template Kategori: INTERNAL
    Untuk surat-surat internal desa seperti:
    - SBALASAN (Balasan)

    Struktur lebih bebas, berbasis body content
--}}
@extends('surat.templates._layout')

@section('content')
    @php
        $nomorSuratMasuk = $data['nomor_surat_masuk'] ?? $data['nomor_rujukan'] ?? null;
        $tanggalSuratMasuk = $data['tanggal_surat_masuk'] ?? null;
        $tanggalSuratMasukText = $tanggalSuratMasuk
            ? \Carbon\Carbon::parse($tanggalSuratMasuk)->locale('id')->translatedFormat('d F Y')
            : null;
        $isiSurat = trim((string) ($data['isi_balasan'] ?? $data['keterangan_tambahan'] ?? ''));
        $isiParagraf = $isiSurat !== ''
            ? preg_split('/\R+/', $isiSurat, -1, PREG_SPLIT_NO_EMPTY)
            : [];
    @endphp

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
        <p class="no-indent" style="margin-bottom: 18px;">
            Kepada Yth.<br>
            <strong>{{ $data['kepada'] ?? '-' }}</strong><br>
            @if (!empty($data['alamat_tujuan']))
                {{ $data['alamat_tujuan'] }}<br>
            @endif
            di Tempat
        </p>
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

    @if ($jenisSurat->kode === 'SBALASAN' && !empty($nomorSuratMasuk))
        <p>
            Menjawab surat Saudara Nomor <strong>{{ $nomorSuratMasuk }}</strong>
            @if ($tanggalSuratMasukText) tanggal <strong>{{ $tanggalSuratMasukText }}</strong>@endif
            @if (!empty($data['perihal'])) perihal <strong>{{ $data['perihal'] }}</strong>@endif,
            dengan ini kami sampaikan hal-hal sebagai berikut.
        </p>
    @endif

    {{-- BODY / ISI SURAT --}}
    @if (!empty($sections['body']) && !($jenisSurat->kode === 'SBALASAN' && !empty($nomorSuratMasuk)))
        <p>{{ $sections['body'] }}</p>
    @endif

    @foreach ($isiParagraf as $paragraf)
        <p>{{ $paragraf }}</p>
    @endforeach

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
