<?php

namespace App\Models;

use App\Services\HashIdService;
use App\Models\Traits\HasHashId;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Spareparts extends Model
{
    use HasFactory, SoftDeletes, Notifiable, HasHashId;

    protected $appends = ['hashid'];
    protected $table = 'spareparts';

    protected $fillable = [
        'nama_part',
        'model',
        'merk',
        'jumlah_baru',
        'jumlah_bekas',
        'supplier',
        'patokan_harga',
        'total',
        'ruk_no',
        'purchase_date',
        'delivery_date',
        'po_number',
        'titik_pesanan',
        'jumlah_pesanan',
        'cek',
        'pic',
        'qr_code',
        'last_notified_at',
        'purchase_request_id'
    ];

    protected $dates = ['deleted_at', 'purchase_date', 'delivery_date', 'last_notified_at'];

    protected $casts = [
    'cek' => 'boolean',
    'patokan_harga' => 'float',
    'total' => 'float',
    'purchase_date' => 'date',
    'delivery_date' => 'date',
    'jumlah_baru' => 'integer',
    'jumlah_bekas' => 'integer',
    'jumlah_pesanan' => 'integer',
    'last_notified_at' => 'datetime',
];


    /* ===========================
     * SETTER METHODS
     * =========================== */
     public function getHashidAttribute()
    {
        return app(HashIdService::class)->encode($this->id);
    }

    public function setJumlahBaruAttribute($value)
    {
        $this->attributes['jumlah_baru'] = (int) $this->normalizeNumber($value);
    }

    public function setJumlahBekasAttribute($value)
    {
        $this->attributes['jumlah_bekas'] = (int) $this->normalizeNumber($value);
    }

    public function setPatokanHargaAttribute($value)
    {
        $this->attributes['patokan_harga'] = (float) $this->normalizeNumber($value);
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = (float) $this->normalizeNumber($value);
    }

    public function setJumlahPesananAttribute($value)
    {
        $this->attributes['jumlah_pesanan'] = (int) $this->normalizeNumber($value);
    }

    public function setPurchaseDateAttribute($value)
    {
        $this->attributes['purchase_date'] = $this->normalizeDate($value);
    }

    public function setDeliveryDateAttribute($value)
    {
        $this->attributes['delivery_date'] = $this->normalizeDate($value);
    }

    public function setCekAttribute($value)
    {
        $this->attributes['cek'] = $this->parseBool($value);
    }

    /* ===========================
     * NORMALIZATION METHODS
     * =========================== */
    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        // Hilangkan karakter selain angka, titik, koma, minus
        $value = preg_replace('/[^0-9.,-]/', '', $value);

        // Ganti koma jadi titik untuk decimal
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? $value : 0;
    }

    private function normalizeDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Invalid date format: " . $value);
            return null;
        }
    }

    private function parseBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? 0;
    }

    /* ===========================
     * RELATIONS
     * =========================== */
    public function pengambilanBarangs()
    {
        return $this->hasMany(Pengambilan::class);
    }
}
