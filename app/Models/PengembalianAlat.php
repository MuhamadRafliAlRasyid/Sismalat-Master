<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PengembalianAlat extends Model
{
    use HasFactory, HasHashId, Notifiable;

    protected $table = 'pengembalian_alats';

    protected $appends = ['hashid'];

    // ✨ PASTIKAN SEMUA FIELD INI ADA DI $fillable
    protected $fillable = [
        'pengambilan_alat_id',  // ← WAJIB ADA
        'user_id',              // ← WAJIB ADA
        'jumlah',               // ← WAJIB ADA
        'tanggal_pengembalian', // ← WAJIB ADA
        'keterangan',           // ← WAJIB ADA
        'foto',                 // ← WAJIB ADA
    ];

    protected $casts = [
        'tanggal_pengembalian' => 'date',
    ];

    /* ================= RELASI ================= */

    public function pengambilan()
    {
        return $this->belongsTo(PengambilanAlat::class, 'pengambilan_alat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ================= ACCESSOR ================= */

    public function getFotoThumbAttribute()
    {
        if (!$this->foto) return null;
        return asset('storage/pengembalian/thumb_' . $this->foto);
    }

    public function getFotoUrlAttribute()
    {
        if (!$this->foto) return null;
        return asset('storage/pengembalian/' . $this->foto);
    }
}
