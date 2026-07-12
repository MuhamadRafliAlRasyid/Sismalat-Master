<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

class PengambilanAlat extends Model
{
    use HasFactory, HasHashId, Notifiable;

    protected $table = 'pengambilan_alats';
    protected $appends = ['hashid'];

    protected $fillable = [
        'user_id',
        'bagian_id',
        'nama_peminjam',
        'alat_id',
        'jumlah',
        'satuan',
        'keperluan',
        'waktu_pengambilan',
        'lama_pinjam',
        'status',
        'foto',
        'last_warned_at',
        'warning_15_sent_at',
        'warning_1day_sent_at',
        'is_critical',
    ];

    protected $casts = [
        'waktu_pengambilan'  => 'datetime',
        'lama_pinjam'        => 'integer',
        'last_warned_at'     => 'datetime',
        'warning_15_sent_at' => 'datetime',
        'warning_1day_sent_at' => 'datetime',
        'is_critical'        => 'boolean',
    ];

    /* ================= RELASI ================= */

    public function alat()
    {
        return $this->belongsTo(Alat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }



public function pengembalians()
{
    return $this->hasMany(PengembalianAlat::class, 'pengambilan_alat_id');
}

    /* ================= ACCESSOR DASAR ================= */

    public function getAlatLabelAttribute()
    {
        if (!$this->alat) return '-';
        return "{$this->alat->nama_alat} | {$this->alat->merk} | {$this->alat->tipe} | {$this->alat->no_seri}";
    }

    public function getStatusLabelAttribute()
    {
        return $this->status === 'dipinjam' ? 'Dipinjam' : 'Dikembalikan';
    }

    public function getTanggalJatuhTempoAttribute()
    {
        if (!$this->waktu_pengambilan || !$this->lama_pinjam) return null;
        return Carbon::parse($this->waktu_pengambilan)->addDays($this->lama_pinjam);
    }

    public function getSisaHariAttribute()
    {
        if (!$this->tanggal_jatuh_tempo) return null;
        if ($this->status === 'dikembalikan') return 0;
        return (int) now()->startOfDay()->diffInDays($this->tanggal_jatuh_tempo->startOfDay(), false);
    }

    public function getSisaHariLabelAttribute()
    {
        $sisa = $this->sisa_hari;
        if ($sisa === null) return '-';
        if ($this->status === 'dikembalikan') return 'Sudah dikembalikan';
        if ($sisa > 0) return "{$sisa} hari lagi";
        if ($sisa === 0) return 'Jatuh tempo hari ini';
        return 'Terlambat ' . abs($sisa) . ' hari';
    }

    public function getPercentaseSisaAttribute()
    {
        if (!$this->lama_pinjam || $this->lama_pinjam <= 0) return 0;
        $sisa = $this->sisa_hari;
        if ($sisa === null || $sisa < 0) return 0;
        return round(($sisa / $this->lama_pinjam) * 100, 2);
    }

    /* ================= ACCESSOR WARNING ================= */

    /**
     * ✨ Warning 15% - belum pernah dikirim atau sudah lewat 3 hari
     */
    public function getShouldWarn15Attribute()
    {
        if ($this->status !== 'dipinjam' || !$this->lama_pinjam || $this->lama_pinjam <= 0) return false;
        $sisa = $this->sisa_hari;
        if ($sisa === null || $sisa < 0) return false;

        $threshold = $this->lama_pinjam * 0.15;
        if ($sisa > $threshold) return false;

        // Belum pernah dikirim ATAU sudah 3 hari sejak terakhir
        if (!$this->warning_15_sent_at) return true;
        return $this->warning_15_sent_at->diffInDays(now()) >= 3;
    }

    /**
     * ✨ Warning 1 Hari - belum pernah dikirim atau belum hari ini
     */
    public function getShouldWarn1DayAttribute()
    {
        if ($this->status !== 'dipinjam' || !$this->lama_pinjam) return false;
        $sisa = $this->sisa_hari;
        if ($sisa === null || $sisa < 0) return false;

        if ($sisa > 1) return false;

        if (!$this->warning_1day_sent_at) return true;
        return !$this->warning_1day_sent_at->isToday();
    }

    /**
     * ✨ Legacy accessor (backward compatible)
     */
    public function getShouldWarnAttribute()
    {
        return $this->should_warn_15;
    }

    /**
     * ✨ Apakah sudah pernah di-notify hari ini? (cegah spam)
     */
    public function getAlreadyWarnedTodayAttribute()
    {
        if (!$this->last_warned_at) {
            return false;
        }
        return $this->last_warned_at->isToday();
    }

    /* ================= STATUS PINJAM ================= */

    public function getStatusPinjamAttribute()
    {
        if ($this->status === 'dikembalikan') {
            return 'selesai';
        }

        $sisa = $this->sisa_hari;

        if ($sisa === null) {
            return 'unknown';
        }

        if ($sisa < 0) {
            return 'terlambat';
        } elseif ($sisa <= 3) {
            return 'warning';
        }

        return 'aman';
    }

    public function isTerlambat(): bool
    {
        return $this->status_pinjam === 'terlambat';
    }

    public function isDipinjam(): bool
    {
        return $this->status === 'dipinjam';
    }

    /* ================= METHOD HELPER ================= */

    /**
     * ✨ Mark warning 15% sudah dikirim
     */
    public function markWarning15Sent(): void
    {
        $this->update([
            'warning_15_sent_at' => now(),
            'last_warned_at'     => now(),
        ]);
    }

    /**
     * ✨ Mark warning 1 hari sudah dikirim (set is_critical = true)
     */
    public function markWarning1DaySent(): void
    {
        $this->update([
            'warning_1day_sent_at' => now(),
            'last_warned_at'       => now(),
            'is_critical'          => true,
        ]);
    }

    /* ================= FOTO ACCESSOR ================= */

    public function getFotoThumbAttribute()
    {
        if (!$this->foto) return null;
        return asset('storage/pengambilan/thumb_' . $this->foto);
    }

    public function getFotoUrlAttribute()
    {
        if (!$this->foto) return null;
        return asset('storage/pengambilan/' . $this->foto);
    }
}
