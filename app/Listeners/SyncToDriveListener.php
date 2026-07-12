<?php

namespace App\Listeners;

use App\Events\SparepartUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Google\Client as Google_Client; // Impor klien Google
use Google\Service\Sheets; // Impor layanan Sheets
use Google\Service\Sheets\ValueRange; // Impor ValueRange dari namespace yang benar

class SyncToDriveListener implements ShouldQueue
{
    public function handle(SparepartUpdated $event)
    {
        $sparepart = $event->sparepart;
        $client = new Google_Client();
        $client->setAccessToken(json_decode(file_get_contents(storage_path('app/token.json')), true));
        $service = new Sheets($client); // Gunakan Sheets dari namespace baru
        $spreadsheetId = '1T50yERh173sKACYDJM7ZHQiaGaqxj2Hu'; // ID dari link Drive
        $range = 'Sheet1!A2:O'; // Sesuaikan range berdasarkan sheet
        $values = [
            [
                $sparepart->nama_part,
                $sparepart->model,
                $sparepart->merk,
                $sparepart->jumlah_baru,
                $sparepart->jumlah_bekas,
                $sparepart->supplier,
                $sparepart->patokan_harga,
                $sparepart->ruk_no,
                $sparepart->purchase_date,
                $sparepart->delivery_date,
                $sparepart->po_number,
                $sparepart->titik_pesanan,
                $sparepart->jumlah_pesanan,
                $sparepart->cek,
                $sparepart->pic,
            ]
        ];
        $body = new ValueRange([ // Gunakan ValueRange dari namespace yang diimpor
            'values' => $values
        ]);
        $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
    }
}
