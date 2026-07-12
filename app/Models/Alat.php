<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Alat extends Model
{
    use HasFactory, SoftDeletes, HasHashId;

    protected $fillable = [
        'nama_alat',
        'kelas',
        'merk',
        'tipe',
        'no_seri',
        'no_identitas',
        'kapasitas',
        'daya_baca',
        'jumlah',
        'no_sertifikat',
        'masa_berlaku',
        'kategori_id',
        'qr_code',
        'foto',
        'last_notified_at',
    ];

    protected $appends = ['hashid', 'status', 'status_hari', 'status_pinjam'];

    protected $casts = [
        'masa_berlaku' => 'date',
    ];

    // ================= RELASI =================
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pengambilan()
    {
        return $this->hasMany(PengambilanAlat::class);
    }

    /**
     * Relasi ke pengembalian melalui pengambilan.
     * Karena pengembalian_alats menyimpan pengambilan_alat_id,
     * bukan langsung alat_id.
     */
    public function pengembalian()
    {
        return $this->hasManyThrough(
            PengembalianAlat::class,
            PengambilanAlat::class,
            'alat_id',                // foreign key di tabel pengambilan_alats
            'pengambilan_alat_id',    // foreign key di tabel pengembalian_alats
            'id',                     // local key di alats
            'id'                      // local key di pengambilan_alats
        );
    }

    public function kalibrasis()
    {
        return $this->hasMany(KalibrasiAlat::class);
    }

    // ================= ACCESSOR =================

    public function getLabelAttribute(): string
    {
        return "{$this->nama_alat} | {$this->merk} | {$this->tipe} | {$this->no_seri}";
    }

    /**
     * Status kalibrasi alat berdasarkan masa berlaku.
     * Return: 'active', 'warning', 'expired', 'unknown'
     */
    public function getStatusAttribute(): string
    {
        if (!$this->masa_berlaku) {
            return 'unknown';
        }

        $today = Carbon::today();
        $expire = Carbon::parse($this->masa_berlaku)->startOfDay();

        if ($expire->isPast()) {
            return 'expired';
        }

        $diffDays = $today->diffInDays($expire, false);
        return $diffDays <= 7 ? 'warning' : 'active';
    }

    /**
     * Selisih hari antara hari ini dengan masa berlaku.
     * Positif = sisa hari menuju kadaluarsa.
     * Negatif = sudah lewat berapa hari.
     */
    public function getStatusHariAttribute(): ?int
    {
        if (!$this->masa_berlaku) {
            return null;
        }

        $today = Carbon::today();
        $expire = Carbon::parse($this->masa_berlaku)->startOfDay();

        return (int) $today->diffInDays($expire, false);
    }

    /**
     * Status peminjaman alat saat ini.
     * Return: 'dipinjam', 'dikembalikan', atau null jika belum pernah dipinjam.
     */
    public function getStatusPinjamAttribute(): ?string
    {
        $pengambilan = $this->pengambilan; // gunakan eager load jika sudah

        if ($pengambilan->isEmpty()) {
            return null;
        }

        return $pengambilan->contains('status', 'dipinjam') ? 'dipinjam' : 'dikembalikan';
    }

    // Accessor foto
    public function getFotoThumbAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/alat/thumb/' . $this->foto);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/alat/' . $this->foto);
    }

    // ================= SCOPE =================
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_alat', 'like', "%$search%")
              ->orWhere('merk', 'like', "%$search%")
              ->orWhere('tipe', 'like', "%$search%")
              ->orWhere('no_seri', 'like', "%$search%")
              ->orWhere('no_identitas', 'like', "%$search%");
        });
    }
}
