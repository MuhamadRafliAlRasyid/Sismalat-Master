<?php

namespace App\Events;

use App\Models\Spareparts;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SparepartCriticalBroadcasted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $sparepart;

    public function __construct(Spareparts $sparepart)
    {
        $this->sparepart = $sparepart;
    }

    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastWith()
    {
        return [
            'nama_part' => $this->sparepart->nama_part,
            'jumlah_baru' => $this->sparepart->jumlah_baru,
            'titik_pesanan' => $this->sparepart->titik_pesanan,
            'message' => "Stok {$this->sparepart->nama_part} kritis!",
        ];
    }
}
