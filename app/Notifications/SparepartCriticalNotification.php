<?php

namespace App\Notifications;

use App\Models\Spareparts;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;

class SparepartCriticalNotification extends Notification
{
    use Queueable;

    protected $sparepart;

    public function __construct(Spareparts $sparepart)
    {
        $this->sparepart = $sparepart;
        Log::info('Creating notification for sparepart ID: ' . $sparepart->hashid);
    }

    public function via($notifiable)
    {
        Log::info('Notification channel for ' . $notifiable->email . ': database');
        return ['database']; // Hanya gunakan database untuk notifikasi web
    }

    public function toDatabase($notifiable)
    {
        if (!$this->sparepart || !$this->sparepart->exists) {
            Log::warning('Sparepart not found for database notification to ' . $notifiable->email);
            return [];
        }
        Log::info('Saving database notification for sparepart ID: ' . $this->sparepart->id . ' to ' . $notifiable->email);
        return [
            'sparepart_id' => $this->sparepart->id,
            'nama_part' => $this->sparepart->nama_part,
            'jumlah_baru' => $this->sparepart->jumlah_baru,
            'titik_pesanan' => $this->sparepart->titik_pesanan,
            'action_url' => route('purchase_requests.create') . '?sparepart_id=' . $this->sparepart->hashid, // Tambahkan sparepart_id
        ];
    }
}
