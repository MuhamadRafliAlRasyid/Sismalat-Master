<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hashid'        => $this->hashid,
            'nama_alat'     => $this->nama_alat,
            'merk'          => $this->merk,
            'tipe'          => $this->tipe,
            'kelas'         => $this->kelas,
            'no_seri'       => $this->no_seri,
            'no_identitas'  => $this->no_identitas,
            'kapasitas'     => $this->kapasitas,
            'daya_baca'     => $this->daya_baca,
            'jumlah'        => $this->jumlah,
            'no_sertifikat' => $this->no_sertifikat,
            'masa_berlaku'  => $this->masa_berlaku?->toDateString(),
            'status'        => $this->status, // dari accessor
            'label'         => $this->label,  // dari accessor

            // Foto (URL utuh)
            'foto_url'      => $this->foto_url,
            'foto_thumb'    => $this->foto_thumb,

            // QR Code (URL utuh)
            'qr_code_url'   => $this->qr_code
                ? asset('storage/' . $this->qr_code)
                : null,

            // Relasi
            'kategori'      => new KategoriResource($this->whenLoaded('kategori')),
            'kalibrasis'    => KalibrasiAlatResource::collection($this->whenLoaded('kalibrasis')),

            // Metadata
            'created_at'    => $this->created_at?->toDateTimeString(),
            'updated_at'    => $this->updated_at?->toDateTimeString(),
            'deleted_at'    => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
