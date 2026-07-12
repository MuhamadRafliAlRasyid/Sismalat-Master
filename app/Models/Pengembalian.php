<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengembalian extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'pengembalian';   // penting karena nama tabel singular

    protected $fillable = [
        'pengambilan_id',
        'sparepart_id',
        'user_id',
        'jumlah_dikembalikan',
        'kondisi',
        'alasan',
        'keterangan',
        'tanggal_kembali',
    ];
    protected $appends = ['hashid'];

    protected $casts = [
        'tanggal_kembali' => 'datetime',
    ];

    // Relasi
    public function user()
{
    return $this->belongsTo(User::class);
}

public function bagian()
{
    return $this->belongsTo(Bagian::class);
}

public function sparepart()
{
    return $this->belongsTo(Spareparts::class, 'sparepart_id');
}

public function pengambilan()
{
    return $this->belongsTo(Pengambilan::class, 'pengambilan_id');
}
public function getRouteKeyName()
{
    return 'id'; // tetap id, tapi kita override binding
}
}
