<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class KalibrasiAlatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $formatTanggal = function ($value) {
            if (!$value) return null;
            if ($value instanceof Carbon) return $value->toDateString();
            return date('Y-m-d', strtotime($value));
        };
        $formatDateTime = function ($value) {
            if (!$value) return null;
            if ($value instanceof Carbon) return $value->toDateTimeString();
            return date('Y-m-d H:i:s', strtotime($value));
        };

        return [
            'hashid'             => $this->hashid,
            'alat_id'            => $this->alat_id,
            // 🔥 Kirim data alat saat relasi dimuat
            'alat'               => $this->whenLoaded('alat', function () {
                return [
                    'nama'       => $this->alat->nama_alat,
                    'foto_thumb' => $this->alat->foto_thumb,
                    'foto_url'   => $this->alat->foto_url,
                ];
            }),
            'tanggal_kalibrasi'  => $formatTanggal($this->tanggal_kalibrasi),
            'masa_berlaku_baru'  => $formatTanggal($this->masa_berlaku_baru),
            'no_sertifikat'      => $this->no_sertifikat,
            'keterangan'         => $this->keterangan,
            'created_at'         => $formatDateTime($this->created_at),
            'updated_at'         => $formatDateTime($this->updated_at),
        ];
    }
}
