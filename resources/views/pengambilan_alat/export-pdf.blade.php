<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>
        @if ($list->count() === 1)
            Bukti Pengambilan Alat
        @else
            Laporan Pengambilan Alat
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

        /* Header dengan desain modern */
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

        .logo-cell {
            width: 80px;
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
            letter-spacing: -0.5px;
        }

        .title-cell p {
            margin: 3px 0;
            color: #64748b;
            font-size: 11px;
        }

        /* Judul Laporan */
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

        /* Info Box untuk single */
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

        .info-box table td {
            padding: 6px 10px;
            border: none;
        }

        .info-box .label {
            font-weight: 700;
            color: #64748b;
            width: 140px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-box .value {
            color: #1e293b;
            font-weight: 600;
        }

        /* Tabel utama */
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
            background-color: #fffbeb;
        }

        .data-table tbody tr:hover {
            background-color: #fef3c7;
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-dipinjam {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .badge-dikembalikan {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        /* Tanda Tangan */
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

        /* Footer Note */
        .footer-note {
            margin-top: 30px;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px dashed #fde68a;
            padding-top: 12px;
            text-align: center;
            font-style: italic;
        }

        /* Nomor halaman */
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
        $logoMime = $logoPath ? mime_content_type($logoPath) : 'image/png';
        $single = $list->count() === 1;
        $first = $list->first();
    @endphp

    {{-- Header --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if ($logoBase64)
                        <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" alt="Logo">
                    @endif
                </td>
                <td class="title-cell" style="padding-left: 15px;">
                    <h1>Dinas Perindustrian &amp; Perdagangan</h1>
                    <p><strong>Kabupaten Karawang</strong></p>
                    <p>Jl. Contoh Alamat No. 123, Karawang, Jawa Barat</p>
                    <p>Telp. (0267) 123456 | Email: perindag@karawangkab.go.id</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Judul --}}
    <div class="report-title">
        @if ($single)
            Bukti Pengambilan Alat
        @else
            Laporan Pengambilan Alat
        @endif
    </div>

    {{-- Info Box untuk single --}}
    @if ($single)
        <div class="info-box">
            <table>
                <tr>
                    <td class="label">Nomor Bukti</td>
                    <td class="value">: {{ $first->hashid }}</td>
                    <td class="label">Tanggal</td>
                    <td class="value">: {{ \Carbon\Carbon::parse($first->waktu_pengambilan)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Pengambil</td>
                    <td class="value">: {{ $first->nama_peminjam ?? ($first->user->name ?? '-') }}</td>
                    <td class="label">Bagian</td>
                    <td class="value">: {{ $first->bagian->nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td class="value" colspan="3">
                        @if ($first->status == 'dipinjam')
                            <span class="badge badge-dipinjam">Sedang Dipinjam</span>
                        @else
                            <span class="badge badge-dikembalikan">Sudah Dikembalikan</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif

    {{-- Tabel Data --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 25%;">Nama Alat</th>
                <th style="width: 15%;">Merk / Tipe</th>
                <th style="width: 12%;">No. Seri</th>
                <th style="width: 8%; text-align: center;">Jumlah</th>
                <th style="width: 8%; text-align: center;">Satuan</th>
                <th style="width: 27%;">Keperluan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list as $i => $d)
                <tr>
                    <td style="text-align: center;">{{ $i + 1 }}</td>
                    <td><strong>{{ $d->alat->nama_alat ?? '-' }}</strong></td>
                    <td>{{ ($d->alat->merk ?? '-') . ' / ' . ($d->alat->tipe ?? '-') }}</td>
                    <td>{{ $d->alat->no_seri ?? '-' }}</td>
                    <td style="text-align: center;">{{ $d->jumlah }}</td>
                    <td style="text-align: center;">{{ $d->satuan ?? 'pcs' }}</td>
                    <td>{{ $d->keperluan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tanda Tangan untuk Single --}}
    @if ($single)
        <table class="signature-section">
            <tr>
                <td>
                    <p class="signature-label">Pengambil,</p>
                    <div class="signature-line">{{ $first->nama_peminjam ?? ($first->user->name ?? '(...)') }}</div>
                </td>
                <td>
                    <p class="signature-label">Petugas Gudang / Admin,</p>
                    <div class="signature-line">( ......................... )</div>
                </td>
            </tr>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer-note">
        * Dokumen ini digenerate secara elektronik oleh Sistem Manajemen Alat (Tanaoroshi) dan sah sebagai bukti resmi.
    </div>
    <div class="page-number">
        Dicetak pada: {{ now()->format('d-m-Y H:i:s') }}
    </div>
</body>

</html>
