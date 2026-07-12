<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hashid'     => $this->hashid,
            'nama'       => $this->nama,
            'keterangan' => $this->keterangan,
        ];
    }
}
