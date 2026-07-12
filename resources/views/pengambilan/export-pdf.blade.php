<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Formulir Pengambilan Sparepart</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            margin: 30px;
            color: #000;
            line-height: 1.5;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header img {
            width: 188px;
            height: 75px;
            float: left;
        }

        .header .info {
            text-align: right;
            margin-left: 70px;
        }

        /* TITLE */
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin: 25px 0;
            text-transform: uppercase;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }

        /* TABLE */
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        .inner-table {
            width: 100%;
            border: none;
        }

        .inner-table th,
        .inner-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        ul {
            margin: 5px 0 0 20px;
            padding: 0;
            list-style-type: disc;
        }

        /* CATATAN */
        .note {
            margin-top: 20px;
            font-size: 12px;
            font-style: italic;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            color: #555;
        }

        /* Responsivitas */
        @media (max-width: 600px) {
            .header {
                text-align: center;
                margin-bottom: 10px;
            }

            .header img {
                width: 60px;
                float: left;
            }

            .header .info {
                text-align: right;
                margin-left: 70px;
            }

            .title {
                font-size: 16px;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 6px;
            }
        }
    </style>
</head>

<body>

    @php
        $logoPath = public_path('images/logo.jpg');
        $logoBase64 = file_exists($logoPath)
            ? base64_encode(file_get_contents($logoPath))
            : base64_encode(file_get_contents(public_path('images/default-logo.jpg')));
        $firstItem = $pengambilanSpareparts->first();
    @endphp


    <div class="header">
        <img src="data:image/jpeg;base64,{{ $logoBase64 }}" alt="Logo PT. Daihatsu"
            onerror="this.src='{{ asset('images/default-logo.jpg') }}'">
        <div class="info">
            <strong>PT. Daihatsu Drive Manufacturing Indonesia</strong><br>
            Jl. Surya Madya VI, Kutanegara, Ciampel, Karawang<br>
            Kec. Ciampel, Karawang,Jawa Barat 41363<br>
        </div>
    </div>

    <!-- TITLE -->
    <div class="title">
        Formulir Pengambilan Sparepart
    </div>

    <!-- MAIN TABLE -->
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Tanggal</th>
                <th style="width: 25%;">Nomor Kanban</th>
                <th style="width: 50%;">Detail</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="3">
                    {{ \Carbon\Carbon::parse($firstItem->created_at)->format('d-m-Y') }}
                </td>
                <td rowspan="3">
                    {{ $firstItem->sparepart->ruk_no ?? '-' }}
                </td>
                <td style="text-align: left; font-weight: bold;">
                    Nama Pengambil
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    {{ $firstItem->user->name ?? 'Tidak diketahui' }}
                </td>
            </tr>
            <tr>
                <td>
                    <table class="inner-table">
                        <tr>
                            <th style="width: 70%; text-align: left;">Nama Mesin / Sparepart</th>
                            <th style="width: 30%;">Jumlah</th>
                        </tr>
                        @foreach ($pengambilanSpareparts as $item)
                            <tr>
                                <td style="text-align: left;">{{ $item->sparepart->nama_part ?? '-' }}</td>
                                <td>{{ $item->jumlah }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="3"
                    style="height: 100px; vertical-align: top; padding: 8px; font-weight: bold; text-align: left;">
                    Alasan Pengambilan:<br>
                    <ul style="font-weight: normal; font-size: 12px;">
                        @foreach ($pengambilanSpareparts as $item)
                            <li>{{ $item->keperluan ?? 'Tidak ada alasan' }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- NOTE -->
    <div class="note">
        Catatan: Dokumen ini adalah bukti resmi pengambilan sparepart dari gudang. Harap disimpan untuk arsip.
    </div>

</body>

</html>
