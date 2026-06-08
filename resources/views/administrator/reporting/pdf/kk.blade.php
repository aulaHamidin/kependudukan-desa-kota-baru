<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Kartu Keluarga</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
        }

        .print-info {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    @include('administrator.reporting.pdf._header')

    <div class="report-title">Laporan Data Kartu Keluarga</div>
    <div class="print-info">Dicetak: {{ $printedAt->format('d/m/Y H:i') }} oleh {{ $user->name }}</div>
    <table>
        <thead>
            <tr>
                <th>No KK</th>
                <th>Kepala</th>
                <th>NIK Kepala</th>
                <th>Anggota</th>
                <th>RT/RW</th>
                <th>Alamat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row->no_kk }}</td>
                    <td>{{ $row->nama_kepala ?? '-' }}</td>
                    <td>{{ $row->nik_kepala ?? '-' }}</td>
                    <td>{{ $row->jumlah_anggota }}</td>
                    <td>RT {{ $row->nomor_rt }} / RW {{ $row->nomor_rw }}</td>
                    <td>{{ $row->alamat }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
