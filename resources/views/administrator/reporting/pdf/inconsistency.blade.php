<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report Data Inconsistency</title>
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

    <div class="report-title">Laporan Data Inconsistency</div>
    <div class="print-info">Dicetak: {{ $printedAt->format('d/m/Y H:i') }} oleh {{ $user->name }}</div>
    <table>
        <thead>
            <tr>
                <th>Issue</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>KK ID</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row->issue_type }}</td>
                    <td>{{ $row->nik }}</td>
                    <td>{{ $row->nama_lengkap }}</td>
                    <td>{{ $row->kartu_keluarga_id ?? '-' }}</td>
                    <td>{{ $row->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
