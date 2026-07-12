<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PengambilanAlat;

class PeminjamanWarningNotification extends Notification
{
    use Queueable;

    protected $pengambilan;
    protected $warningType;

    public function __construct(PengambilanAlat $pengambilan, string $warningType = '15_percent')
    {
        $this->pengambilan = $pengambilan;
        $this->warningType = $warningType;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sisaHari = $this->pengambilan->sisa_hari;
        $persentase = $this->pengambilan->percentase_sisa;
        $jatuhTempo = $this->pengambilan->tanggal_jatuh_tempo;

        $subject = $this->warningType === '1_day'
            ? '🔴 URGENT: Pengembalian Alat Besok!'
            : '⚠️ Peringatan: Peminjaman Alat Segera Berakhir';

        $message = $this->warningType === '1_day'
            ? "Peminjaman alat Anda akan jatuh tempo BESOK. Segera kembalikan alat untuk menghindari keterlambatan."
            : "Peminjaman alat Anda akan segera berakhir. Sisa waktu: {$sisaHari} hari ({$persentase}% tersisa).";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line($message)
            ->line('**Detail Peminjaman:**')
            ->line([
                'Nama Alat' => $this->pengambilan->alat->nama_alat ?? 'Alat',
                'Merk' => $this->pengambilan->alat->merk ?? '-',
                'Sisa Waktu' => $this->pengambilan->sisa_hari_label,
                'Jatuh Tempo' => $jatuhTempo?->format('d M Y'),
            ])
            ->line('Segera kembalikan alat atau lakukan perpanjangan peminjaman.')
            ->action('Kembalikan Alat Sekarang', route('pengembalian_alat.create', $this->pengambilan->hashid))
            ->line('Terima kasih telah menggunakan sistem kami.');
    }

    public function toArray(object $notifiable): array
    {
        $alat = $this->pengambilan->alat;
        $sisaHari = $this->pengambilan->sisa_hari;
        $persentase = $this->pengambilan->percentase_sisa;
        $jatuhTempo = $this->pengambilan->tanggal_jatuh_tempo;

        $actionUrl = route('pengembalian_alat.create', $this->pengambilan->hashid);

        if ($this->warningType === '1_day') {
            return [
                'type'            => 'peminjaman_warning_1day',
                'pengambilan_id'  => $this->pengambilan->id,
                'hashid'          => $this->pengambilan->hashid,
                'nama_alat'       => $alat->nama_alat ?? 'Alat',
                'merk'            => $alat->merk ?? '-',
                'sisa_hari'       => $sisaHari,
                'persentase_sisa' => $persentase,
                'jatuh_tempo'     => $jatuhTempo?->format('d M Y'),
                'message'         => "🔴 URGENT: Peminjaman alat \"{$alat->nama_alat}\" jatuh tempo besok ({$jatuhTempo?->format('d M Y')}). Klik untuk segera kembalikan!",
                'action_url'      => $actionUrl,
                'action_label'    => 'Kembalikan Alat',
                'icon'            => 'exclamation-circle',
                'color'           => 'red',
                'priority'        => 'high',
            ];
        }

        return [
            'type'            => 'peminjaman_warning',
            'pengambilan_id'  => $this->pengambilan->id,
            'hashid'          => $this->pengambilan->hashid,
            'nama_alat'       => $alat->nama_alat ?? 'Alat',
            'merk'            => $alat->merk ?? '-',
            'sisa_hari'       => $sisaHari,
            'persentase_sisa' => $persentase,
            'jatuh_tempo'     => $jatuhTempo?->format('d M Y'),
            'message'         => "⚠️ Peminjaman alat \"{$alat->nama_alat}\" tersisa {$sisaHari} hari ({$persentase}%). Klik untuk segera kembalikan!",
            'action_url'      => $actionUrl,
            'action_label'    => 'Kembalikan Alat',
            'icon'            => 'exclamation-triangle',
            'color'           => 'orange',
            'priority'        => 'normal',
        ];
    }
}
