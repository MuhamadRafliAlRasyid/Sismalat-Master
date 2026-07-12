<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestLog extends Model
{
    use HasFactory;
    use HasHashId;

    protected $table = 'request_logs';

    protected $fillable = [
        'purchaserequest_id',
        'approved_by',
        'action',
        'notes',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
