<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengambilanAlatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hashid'             => $this->hashid,
            'user_id'            => $this->user_id,
            'bagian_id'          => $this->bagian_id,
            'nama_peminjam'      => $this->nama_peminjam,
            'alat_id'            => $this->alat_id,
            'jumlah'             => $this->jumlah,
            'satuan'             => $this->satuan,
            'keperluan'          => $this->keperluan,
            'waktu_pengambilan'  => $this->waktu_pengambilan?->toDateTimeString(),
            'status'             => $this->status,
            'status_label'       => $this->status_label, // dari accessor
            'alat_label'         => $this->alat_label,   // dari accessor

            // Foto
            'foto_url'           => $this->foto_url,
            'foto_thumb'         => $this->foto_thumb,

            // Relasi
            'alat'               => new AlatResource($this->whenLoaded('alat')),
            'user'               => new UserResource($this->whenLoaded('user')),
            'bagian'             => new BagianResource($this->whenLoaded('bagian')),
            'pengembalians'      => PengembalianAlatResource::collection($this->whenLoaded('pengembalians')),

            // Metadata
            'created_at'         => $this->created_at?->toDateTimeString(),
            'updated_at'         => $this->updated_at?->toDateTimeString(),
        ];
    }
}
