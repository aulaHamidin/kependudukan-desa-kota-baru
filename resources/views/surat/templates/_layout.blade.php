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
            margin: 2.5cm 2cm 2cm 2.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
        }

        .surat-container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 0;
        }

        /* KOP SURAT */
        .kop-surat {
            text-align: center;
            padding-bottom: 8px;
            margin-bottom: 0;
        }

        .kop-border {
            border-bottom: 3px solid #000;
            margin-bottom: 1px;
        }

        .kop-border-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }

        .kop-surat .instansi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .kop-surat .nama-desa {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .kop-surat .alamat {
            font-size: 10pt;
            margin-top: 5px;
        }

        /* JUDUL SURAT */
        .judul-surat {
            text-align: center;
            margin: 30px 0;
        }

        .judul-surat h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 1px;
        }

        .judul-surat .nomor-surat {
            font-size: 12pt;
            margin-top: 5px;
        }

        /* CONTENT */
        .content {
            text-align: justify;
            margin: 20px 0;
        }

        .content p {
            margin-bottom: 12px;
            text-indent: 1.5cm;
        }

        .content p.no-indent {
            text-indent: 0;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            margin: 15px 0;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .data-table td.number {
            width: 5%;
            text-align: right;
            padding-right: 8px;
        }

        .data-table td.label {
            width: 28%;
            padding-right: 10px;
        }

        .data-table td.separator {
            width: 3%;
            text-align: center;
        }

        .data-table td.value {
            width: 64%;
        }

        /* SIGNATURE */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-block {
            width: 45%;
            text-align: center;
        }

        .signature-block.right {
            float: right;
        }

        .signature-block.left {
            float: left;
        }

        .signature-block .tempat-tanggal {
            margin-bottom: 5px;
        }

        .signature-block .jabatan {
            font-weight: bold;
            margin-bottom: 60px;
        }

        .signature-block .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-block .nip {
            font-size: 10pt;
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
            margin-top: 10px;
        }

        .mt-2 {
            margin-top: 20px;
        }

        .mt-3 {
            margin-top: 30px;
        }

        .mb-1 {
            margin-bottom: 10px;
        }

        .mb-2 {
            margin-bottom: 20px;
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

            .surat-container {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="surat-container">
        {{-- KOP SURAT --}}
        @include('surat.templates.partials._kop')

        {{-- JUDUL & NOMOR SURAT --}}
        <div class="judul-surat">
            <h1>{{ $jenisSurat->nama }}</h1>
            @if (isset($suratTerbit) && $suratTerbit->nomor_surat)
                <p class="nomor-surat">Nomor: {{ $suratTerbit->nomor_surat }}</p>
            @endif
        </div>

        {{-- CONTENT - Di-override oleh template kategori --}}
        <div class="content">
            @yield('content')
        </div>

        {{-- PENUTUP - bisa di-override oleh template kategori --}}
        @hasSection('closing')
            @yield('closing')
        @else
            @if ($sections['masa_berlaku'] ?? false)
                <p class="mt-2">
                    Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya
                    dan berlaku selama <strong>{{ $jenisSurat->masa_berlaku_hari }} hari</strong>
                    sejak tanggal diterbitkan.
                </p>
            @else
                <p class="mt-2">
                    Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
                </p>
            @endif
        @endif

        {{-- SIGNATURE --}}
        <div class="signature-section clearfix">
            @include($jenisSurat->getSignaturePartialPath())
        </div>
    </div>

    @stack('scripts')
</body>

</html>
