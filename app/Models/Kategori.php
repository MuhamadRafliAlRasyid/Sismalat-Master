<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Traits\HasHashId;

class Kategori extends Model
{
    use HasHashId, HasFactory;

    protected $fillable = ['nama','keterangan'];

    protected $appends = ['hashid'];

    public function alats()
    {
        return $this->hasMany(Alat::class);
    }
}
