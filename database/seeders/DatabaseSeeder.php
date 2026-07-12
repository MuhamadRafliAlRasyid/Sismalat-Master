<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Spareparts;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Spareparts::create([
            'nama_part'       => 'Testing Part',
            'model'           => 'NS40',
            'merk'            => 'GS Astra',
            'jumlah_baru'     => 50,
            'jumlah_bekas'    => 10,
            'supplier'        => 'testing supplier',
            'patokan_harga'   => 750000,
            'total'           => 37500000,
            'ruk_no'          => 'RUK-001',
            'purchase_date'   => '2024-01-15',
            'delivery_date'   => '2024-01-20',
            'po_number'       => 'PO-INV-001',
            'titik_pesanan'   => 'Gudang Utama',
            'jumlah_pesanan'  => 50,
            'cek'             => true,
            'pic'             => 'Admin',
            'qr_code'         => null,
        ]);
    }
}
