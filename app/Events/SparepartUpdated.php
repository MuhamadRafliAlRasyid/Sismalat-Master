<?php

namespace App\Events;

use App\Models\Sparepart;
use App\Models\Spareparts;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SparepartUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sparepart;

    public function __construct(Spareparts $sparepart)
    {
        $this->sparepart = $sparepart;
    }
}
