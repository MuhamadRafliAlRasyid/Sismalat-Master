<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bagian extends Model
{
    use HasFactory;
    use HasHashId;

    protected $table = 'bagian';

    protected $fillable = ['nama'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function pengambilanBarangs()
    {
        return $this->hasMany(Pengambilan::class);
    }
}
