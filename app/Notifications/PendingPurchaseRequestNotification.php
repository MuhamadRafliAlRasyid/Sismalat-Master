<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class PendingPurchaseRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
        Log::info('Creating pending PR notification for PR ID: ' . $purchaseRequest->hashid);
    }

    public function via($notifiable)
    {
        Log::info('Pending PR notification channel for ' . $notifiable->email . ': database');
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        if (!$this->purchaseRequest || !$this->purchaseRequest->exists) {
            Log::warning('Purchase Request not found for notification to ' . $notifiable->email);
            return [];
        }

        Log::info('Saving pending PR notification for PR ID: ' . $this->purchaseRequest->id . ' to ' . $notifiable->email);

        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'pr_hashid' => $this->purchaseRequest->hashid,
            'nama_part' => $this->purchaseRequest->nama_part,
            'part_number' => $this->purchaseRequest->part_number,
            'quantity' => $this->purchaseRequest->quantity,
            'satuan' => $this->purchaseRequest->satuan,
            'pic' => $this->purchaseRequest->pic,
            'status' => $this->purchaseRequest->status,
            'created_by' => $this->purchaseRequest->user->name ?? 'Unknown',
            'created_at' => $this->purchaseRequest->created_at->toDateTimeString(),
            'message' => 'Purchase Request menunggu approval',
            'action_url' => route('purchase_requests.show', $this->purchaseRequest->hashid),
            'type' => 'pending_purchase_request'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'purchase_request_id' => $this->purchaseRequest->id,
            'nama_part' => $this->purchaseRequest->nama_part,
            'message' => 'Purchase Request baru menunggu approval: ' . $this->purchaseRequest->nama_part,
            'action_url' => route('purchase_requests.show', $this->purchaseRequest->hashid),
        ];
    }
}
