<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'id' => $this->hashid,
        'name' => $this->name,
        'email' => $this->email,
        'role' => $this->role,
        'bagian_id' => $this->bagian?->nama,

        // 🔥 WAJIB TAMBAH INI
        'profile_photo_path' => $this->profile_photo_path,

        // BONUS (lebih enak dipakai Flutter)
        'profile_photo_url' => $this->profile_photo_path
    ? Storage::url($this->profile_photo_path)
    : null,
    ];
}
}
