<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SparepartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashid,
            'nama' => $this->nama_part,
            'stok' => $this->jumlah_baru,
            'merk' => $this->merk,
            'total' => $this->total,
        ];
    }
}
