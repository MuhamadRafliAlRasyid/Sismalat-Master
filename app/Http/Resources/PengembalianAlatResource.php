<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengembalianAlatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hashid'                => $this->hashid,
            'pengambilan_alat_id'   => $this->pengambilan_alat_id,
            'user_id'               => $this->user_id,
            'nama_peminjam'         => $this->nama_peminjam,
            'jumlah'                => $this->jumlah,
            'tanggal' => $this->tanggal_kalibrasi instanceof \Carbon\Carbon
    ? $this->tanggal_kalibrasi->toDateString()
    : $this->tanggal_kalibrasi,
            'keterangan'            => $this->keterangan,

            // Foto
            'foto_url'              => $this->foto_url,
            'foto_thumb'            => $this->foto_thumb,

            // Relasi
            'pengambilan'           => new PengambilanAlatResource($this->whenLoaded('pengambilan')),
            'user'                  => new UserResource($this->whenLoaded('user')),

            // Metadata
            'created_at'            => $this->created_at?->toDateTimeString(),
            'updated_at'            => $this->updated_at?->toDateTimeString(),
        ];
    }
}
