<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>
        @if ($list->count() === 1)
            Bukti Pengembalian Alat
        @else
            Laporan Pengembalian Alat
        @endif
    </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            margin: 30px;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #fffbeb 0%, #fff7ed 100%);
            border-radius: 16px;
            padding: 20px 25px;
            margin-bottom: 25px;
            border: 1px solid #fde68a;
            position: relative;
            overflow: hidden;
        }

        .header::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 50%;
        }

        .header-table {
            width: 100%;
            border: none;
            position: relative;
            z-index: 1;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .logo-cell img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 12px;
            border: 2px solid #fde68a;
            padding: 5px;
            background: white;
        }

        .title-cell h1 {
            font-size: 20px;
            color: #92400e;
            margin: 0 0 6px 0;
            font-weight: 800;
        }

        .title-cell p {
            margin: 3px 0;
            color: #64748b;
            font-size: 11px;
        }

        .report-title {
            text-align: center;
            font-weight: 800;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 25px 0;
            color: #b45309;
            position: relative;
            padding-bottom: 15px;
        }

        .report-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #f59e0b, #f97316);
            border-radius: 2px;
        }

        .info-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .info-box table {
            width: 100%;
            border: none;
        }

        .info-box td {
            padding: 6px 10px;
            border: none;
        }

        .label {
            font-weight: 700;
            color: #64748b;
            width: 140px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            color: #1e293b;
            font-weight: 600;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .data-table thead th {
            background: linear-gradient(to bottom, #fef3c7, #fde68a);
            color: #92400e;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            border-bottom: 2px solid #f59e0b;
            text-align: left;
        }

        .data-table tbody td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            color: #334155;
        }

        .data-table tbody tr:nth-child(even) {
            background: #fffbeb;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .summary-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 12px 15px;
            margin-top: 15px;
            font-size: 12px;
            color: #92400e;
        }

        .signature-section {
            width: 100%;
            margin-top: 50px;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 20px;
        }

        .signature-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signature-line {
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px dashed #fde68a;
            padding-top: 12px;
            text-align: center;
            font-style: italic;
        }

        .page-number {
            text-align: right;
            font-size: 10px;
            color: #94a3b8;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('images/logo-metrologi.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logos.jpg');
        }
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo.jpg');
        }
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $single = $list->count() === 1;
        $first = $list->first();
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell" style="width:80px;">
                    @if ($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
                    @endif
                </td>
                <td class="title-cell" style="padding-left:15px;">
                    <h1>Dinas Perindustrian &amp; Perdagangan</h1>
                    <p><strong>Kabupaten Karawang</strong></p>
                    <p>Jl. Contoh Alamat No. 123, Karawang, Jawa Barat</p>
                    <p>Telp. (0267) 123456 | Email: perindag@karawangkab.go.id</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">
        @if ($single)
            Bukti Pengembalian Alat
        @else
            Laporan Pengembalian Alat
        @endif
    </div>

    @if ($single)
        <div class="info-box">
            <table>
                <tr>
                    <td class="label">Nomor Bukti</td>
                    <td class="value">: {{ $first->hashid }}</td>
                    <td class="label">Tanggal</td>
                    <td class="value">: {{ \Carbon\Carbon::parse($first->tanggal_pengembalian)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Pengembali</td>
                    <td class="value">: {{ $first->nama_peminjam ?? ($first->user->name ?? '-') }}</td>
                    <td class="label">Bagian</td>
                    <td class="value">: {{ $first->pengambilan->bagian->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Alat</td>
                    <td class="value" colspan="3">: {{ $first->pengambilan->alat->nama_alat ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value" colspan="3"><span class="badge">Dikembalikan</span></td>
                </tr>
            </table>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th style="text-align:center;">No</th>
                <th>Alat</th>
                <th>Merk / Tipe</th>
                <th>Pengguna</th>
                <th style="text-align:center;">Jumlah</th>
                <th style="text-align:center;">Tanggal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list as $i => $d)
                <tr>
                    <td style="text-align:center;">{{ $i + 1 }}</td>
                    <td><strong>{{ $d->pengambilan->alat->nama_alat ?? '-' }}</strong></td>
                    <td>{{ ($d->pengambilan->alat->merk ?? '-') . ' / ' . ($d->pengambilan->alat->tipe ?? '-') }}</td>
                    <td>{{ $d->nama_peminjam ?? ($d->user->name ?? '-') }}</td>
                    <td style="text-align:center; font-weight:bold; color:#b45309;">{{ $d->jumlah }}</td>
                    <td style="text-align:center;">
                        {{ \Carbon\Carbon::parse($d->tanggal_pengembalian)->format('d-m-Y') }}</td>
                    <td>{{ $d->keterangan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!$single)
        <div class="summary-box">
            <strong>Total Data:</strong> {{ $list->count() }} pengembalian |
            <strong>Total Barang Dikembalikan:</strong> {{ $list->sum('jumlah') }} unit |
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($list->min('tanggal_pengembalian'))->format('d M Y') }}
            - {{ \Carbon\Carbon::parse($list->max('tanggal_pengembalian'))->format('d M Y') }}
        </div>
    @endif

    @if ($single)
        <table class="signature-section">
            <tr>
                <td>
                    <p class="signature-label">Pengembali,</p>
                    <div class="signature-line">{{ $first->nama_peminjam ?? ($first->user->name ?? '(...)') }}</div>
                </td>
                <td>
                    <p class="signature-label">Petugas Gudang / Admin,</p>
                    <div class="signature-line">( ......................... )</div>
                </td>
            </tr>
        </table>
    @endif

    <div class="footer-note">
        * Dokumen ini digenerate secara elektronik oleh Sistem Manajemen Alat (Tanaoroshi) dan sah sebagai bukti resmi
        pengembalian.
    </div>
    <div class="page-number">
        Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
    </div>
</body>

</html>
