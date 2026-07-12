<?php

namespace App\Services;

use Hashids\Hashids;

class HashIdService
{
    protected Hashids $hashids;

    public function __construct()
{
    // Menggunakan salt khusus dari .env, jika tidak ada baru fallback ke app.key
    $salt = env('HASHIDS_SALT', config('app.key'));

    $this->hashids = new Hashids($salt, 10);
}

    public function encode(int $id): string
    {
        return $this->hashids->encode($id);
    }

    public function decode(string $hash): ?int
    {
        $decoded = $this->hashids->decode($hash);
        return !empty($decoded) ? $decoded[0] : null;
    }
}
