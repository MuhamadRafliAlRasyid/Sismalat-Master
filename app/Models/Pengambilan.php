<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengambilan extends Model
{
    use HasFactory, HasHashId;
    protected $appends = ['hashid'];

    protected $table = 'pengambilan';
    protected $fillable = [
    'user_id',
    'bagian_id',
    'spareparts_id', // tetap ada (backward)
    'item_type',
    'item_id',
    'part_type',
    'jumlah',
    'jumlah_dikembalikan',
    'satuan',
    'keperluan',
    'waktu_pengambilan'
];

public function scopeFindByHashidOrFail($query, $hashid)
{
    $decoded = app(\App\Services\HashIdService::class)->decode($hashid);
    if (!$decoded) abort(404);

    return $query->findOrFail($decoded);
}
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
        return $this->belongsTo(Spareparts::class, 'spareparts_id');

    }

public function pengembalian()
{
    return $this->hasMany(Pengembalian::class, 'pengambilan_id');
    // ← Tambahkan foreign key secara eksplisit
}
public function item()
{
    if ($this->item_type === 'alat') {
        return $this->belongsTo(\App\Models\Alat::class, 'item_id');
    }

    return $this->belongsTo(\App\Models\Spareparts::class, 'item_id');
}
}
