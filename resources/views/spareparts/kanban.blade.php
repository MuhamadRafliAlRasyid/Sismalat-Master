<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
            margin: 20px auto;
        }

        th,
        td {
            border: 1.5px solid black;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .header-cell {
            font-weight: bold;
            font-size: 18px;
            text-align: center;
        }

        .header-kanban {
            background-color: red;
            color: white;
        }

        .logo {
            width: 140px;
            height: auto;
        }

        .qr-img {
            width: 70px;
            height: 70px;
            margin-top: 4px;
        }

        .label {
            text-align: left;
            font-weight: bold;
            padding-left: 8px;
            width: 150px;
        }

        .value {
            font-weight: bold;
            font-size: 18px;
        }

        /* Jarak antar tabel */
        .table-gap {
            margin-top: 50px;
        }
    </style>
</head>

<body>

    {{-- ========== BARANG ASLI KANBAN ========== --}}
    <table>
        <tr>
            <!-- Logo -->
            <td style="width:160px; border:none; text-align:center;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.jpg'))) }}"
                    class="logo">
            </td>

            <!-- Title -->
            <td class="header-cell" style="width:60%;">BARANG ASLI KANBAN</td>

            <!-- Info + QR -->
            <td style="width:140px; border:none; text-align:center;">
                Nomor urut: <b>{{ $sparepart->ruk_no }}</b><br>
                Rak No: <b>{{ $sparepart->ruk_no }}</b><br>
                @if ($sparepart->qr_code && file_exists(storage_path('app/public/' . $sparepart->qr_code)))
                    <img src="{{ storage_path('app/public/' . $sparepart->qr_code) }}" class="qr-img">
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Nama Produk</td>
            <td colspan="2" class="value">{{ $sparepart->nama_part }}</td>
        </tr>
        <tr>
            <td class="label">Model</td>
            <td colspan="2" class="value">{{ $sparepart->model ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pabrikan</td>
            <td colspan="2" class="value">{{ $sparepart->merk ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Titik pesanan</td>
            <td class="value">{{ $sparepart->titik_pesanan ?? 0 }}</td>
            <td class="label">Jumlah pesanan: {{ $sparepart->jumlah_pesanan ?? 0 }}</td>
        </tr>
    </table>

    {{-- ========== PESANAN KANBAN ========== --}}
    <table class="table-gap">
        <tr>
            <!-- Logo -->
            <td style="width:160px; border:none; text-align:center;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.jpg'))) }}"
                    class="logo">
            </td>

            <!-- Title -->
            <td class="header-cell header-kanban" style="width:60%;">PESANAN KANBAN</td>

            <!-- Info + QR -->
            <td style="width:140px; border:none; text-align:center;">
                Nomor urut: <b>{{ $sparepart->ruk_no }}</b><br>
                Rak No: <b>{{ $sparepart->ruk_no }}</b><br>
                @if ($sparepart->qr_code && file_exists(storage_path('app/public/' . $sparepart->qr_code)))
                    <img src="{{ storage_path('app/public/' . $sparepart->qr_code) }}" class="qr-img">
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Nama Produk</td>
            <td colspan="2" class="value">{{ $sparepart->nama_part }}</td>
        </tr>
        <tr>
            <td class="label">Model</td>
            <td colspan="2" class="value">{{ $sparepart->model ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pabrikan</td>
            <td colspan="2" class="value">{{ $sparepart->merk ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Titik pesanan</td>
            <td class="value">{{ $sparepart->titik_pesanan ?? 0 }}</td>
            <td class="label">Jumlah pesanan: {{ $sparepart->jumlah_pesanan ?? 0 }}</td>
        </tr>
    </table>

</body>

</html>
