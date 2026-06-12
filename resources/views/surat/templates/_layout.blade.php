{{--
    Master Layout untuk semua template surat
    Digunakan oleh semua template kategori (_keterangan, _pengantar, dll)

    Variables yang diterima:
    - $jenisSurat: Model JenisSurat
    - $sections: Array konfigurasi dari template_sections
    - $fieldLabels: Array label untuk setiap field
    - $data: Array data penduduk dan tambahan
    - $suratTerbit: Model SuratTerbit (jika ada)
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $jenisSurat->nama }} - {{ $data['nama_lengkap'] ?? 'Surat' }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.15cm 1.25cm 1.15cm 1.25cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.24;
            color: #000;
            background: #fff;
        }

        .surat-container {
            max-width: 18.9cm;
            margin: 0 auto;
            padding: 0.15cm 0.2cm;
        }

        /* KOP SURAT */
        .kop-surat {
            text-align: center;
            padding-bottom: 3px;
            margin-bottom: 0;
        }

        .kop-border {
            border-bottom: 2px solid #000;
            margin-bottom: 1px;
        }

        .kop-border-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
        }

        .kop-surat .instansi {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4px;
            line-height: 1.05;
        }

        .kop-surat .nama-desa {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1.05;
        }

        .kop-surat .alamat {
            font-size: 8.5pt;
            line-height: 1.1;
        }

        /* JUDUL SURAT */
        .judul-surat {
            text-align: center;
            margin: 10px 0 9px;
        }

        .judul-surat h1 {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: .5px;
        }

        .judul-surat .nomor-surat {
            font-size: 10.5pt;
            margin-top: 1px;
        }

        /* CONTENT */
        .content {
            text-align: justify;
            margin: 8px 0 0;
        }

        .content p {
            margin-bottom: 6px;
            text-indent: 1cm;
        }

        .content p.hanging {
            padding-left: 1cm;
            text-indent: -1cm;
        }

        .content p.no-indent {
            text-indent: 0;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            margin: 5px 0 7px;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .data-table td.number {
            width: 4%;
            text-align: right;
            padding-right: 5px;
        }

        .data-table td.label {
            width: 27%;
            padding-right: 6px;
        }

        .data-table td.separator {
            width: 2%;
            text-align: center;
        }

        .data-table td.value {
            width: 67%;
        }

        /* SIGNATURE */
        .signature-section {
            margin-top: 10px;
            page-break-inside: avoid;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .signature-spacer {
            width: 50%;
        }

        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-cell .tempat-tanggal {
            margin-bottom: 2px;
        }

        .signature-cell .jabatan {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .signature-cell .ruang-ttd {
            height: 48px;
            margin: 4px 0;
        }

        .signature-cell .nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }

        .signature-cell .nip {
            font-size: 10pt;
            margin-top: 2px;
        }

        /* UTILITIES */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-underline {
            text-decoration: underline;
        }

        .mt-1 {
            margin-top: 6px;
        }

        .mt-2 {
            margin-top: 10px;
        }

        .mt-3 {
            margin-top: 16px;
        }

        .mb-1 {
            margin-bottom: 6px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .surat-sktm {
            font-size: 10.4pt;
            line-height: 1.18;
        }

        .surat-sktm .data-table {
            margin: 3px 0 5px;
        }

        .surat-sktm .data-table td {
            padding: 0;
        }

        .surat-sktm .content p {
            margin-bottom: 4px;
        }

        .surat-sku .data-table,
        .surat-skd .data-table {
            margin: 4px 0 6px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* PRINT STYLES */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="surat-container surat-{{ strtolower($jenisSurat->kode) }}">
        {{-- KOP SURAT --}}
        @include('surat.templates.partials._kop')

        {{-- JUDUL & NOMOR SURAT --}}
        <div class="judul-surat">
            <h1>{{ $jenisSurat->nama }}</h1>
            @if (isset($suratTerbit) && $suratTerbit->nomor_surat)
                <p class="nomor-surat">Nomor : {{ $suratTerbit->nomor_surat }}</p>
            @endif
        </div>

        {{-- CONTENT - Di-override oleh template kategori --}}
        <div class="content">
            @yield('content')
        </div>

        {{-- PENUTUP - bisa di-override oleh template kategori --}}
        @php
            $suppressDefaultClosing = ($sections['suppress_default_closing'] ?? false)
                || in_array($jenisSurat->kode, ['SKD', 'SKTM', 'SKU'], true);
        @endphp
        @if (!$suppressDefaultClosing)
            @hasSection('closing')
                @yield('closing')
            @else
                @php
                    $masaBerlakuHari = $suratTerbit?->masa_berlaku_hari ?? $jenisSurat->masa_berlaku_hari;
                    $closingText = $sections['closing'] ?? null;
                @endphp
                @if ($closingText)
                    <p class="mt-2">
                        {{ $closingText }}
                    </p>
                @elseif ($masaBerlakuHari)
                    <p class="mt-2">
                        Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya
                        dan berlaku selama <strong>{{ $masaBerlakuHari }} hari</strong>
                        sejak tanggal diterbitkan.
                    </p>
                @else
                    <p class="mt-2">
                        Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
                    </p>
                @endif
            @endif
        @endif

        {{-- SIGNATURE --}}
        <div class="signature-section">
            @include($jenisSurat->getSignaturePartialPath())
        </div>
    </div>

    @stack('scripts')
</body>

</html>
