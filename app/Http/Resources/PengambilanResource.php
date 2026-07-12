<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengambilanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashid,
            'user' => $this->user->name,
            'barang' => $this->sparepart->nama_part,
            'jumlah' => $this->jumlah,
            'keperluan' => $this->keperluan,
            'tanggal' => $this->waktu_pengambilan,
        ];
    }
}
