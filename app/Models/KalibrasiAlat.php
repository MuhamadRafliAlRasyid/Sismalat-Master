<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;

class KalibrasiAlat extends Model
{
    use HasHashId;

    protected $appends = ['hashid'];

    protected $fillable = [
        'alat_id',
        'tanggal_kalibrasi',
        'masa_berlaku_baru',
        'no_sertifikat',
        'keterangan',
    ];

    public function alat()
    {
        return $this->belongsTo(Alat::class);
    }
}
