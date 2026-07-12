<?php

namespace App\Notifications;

use App\Models\Alat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// ✅ PERBAIKAN: HAPUS "implements ShouldQueue"
class AlatExpiredNotification extends Notification
{
    use Queueable;

    protected Alat $alat;
    protected string $status;

    public function __construct(Alat $alat, string $status)
    {
        $this->alat = $alat;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $sisaHari = null;
        $jatuhTempo = null;

        if ($this->alat->masa_berlaku) {
            $jatuhTempoDate = Carbon::parse($this->alat->masa_berlaku);
            $sisaHari = now()->diffInDays($jatuhTempoDate, false);
            $jatuhTempo = $jatuhTempoDate->format('d M Y');
        }

        return [
            'type'            => 'alat_kalibrasi',
            'alat_id'         => $this->alat->id,
            'alat_hashid'     => $this->alat->hashid,
            'nama_alat'       => $this->alat->nama_alat,
            'merk'            => $this->alat->merk ?? '-',
            'tipe'            => $this->alat->tipe ?? '-',
            'status'          => $this->status,
            'message'         => $this->getMessage(),
            'action_url'      => route('kalibrasis.create', $this->alat->hashid),
            'action_label'    => 'Kalibrasi Sekarang',
            'sisa_hari'       => $sisaHari,
            'jatuh_tempo'     => $jatuhTempo,
            'icon'            => $this->getIcon(),
            'color'           => $this->getColor(),
            'priority'        => $this->getPriority(),
            'created_by'      => 'system',
            'source'          => 'checkExpired',
        ];
    }

    protected function getMessage(): string
    {
        if ($this->status === 'expired') {
            return "🔴 URGENT: Alat \"{$this->alat->nama_alat}\" sudah kadaluarsa. Segera lakukan kalibrasi!";
        }

        $sisaHari = now()->diffInDays(Carbon::parse($this->alat->masa_berlaku));
        return "⚠️ Alat \"{$this->alat->nama_alat}\" akan kadaluarsa dalam {$sisaHari} hari. Siapkan kalibrasi.";
    }

    protected function getIcon(): string
    {
        return $this->status === 'expired' ? 'exclamation-circle' : 'exclamation-triangle';
    }

    protected function getColor(): string
    {
        return $this->status === 'expired' ? 'red' : 'orange';
    }

    protected function getPriority(): string
    {
        return $this->status === 'expired' ? 'high' : 'normal';
    }
}
