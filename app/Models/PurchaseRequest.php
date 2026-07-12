<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseRequest extends Model
{
    use HasFactory, HasHashId;

    protected $appends = ['hashid'];
    protected $table = 'purchase_requests';

    protected $fillable = [
        'user_id',
        'nama_part',
        'part_number',
        'link_website',
        'waktu_request',
        'quantity',
        'satuan',
        'mas_deliver',
        'untuk_apa',
        'pic',
        'quotation_lead_time',
        'status',
        'last_notified_at',
        'sparepart_id'        // ← pastikan ini ada
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(RequestLog::class);
    }


    public function sparepart()
    {
        return $this->belongsTo(Spareparts::class, 'sparepart_id');   // ← PERBAIKAN DI SINI
    }
}
