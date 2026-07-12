<?php

namespace App\Http\Controllers;

use App\Models\RequestLog;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class SuperDashboardController extends BaseController
{
    public function __construct() {}

    public function index()
    {
        $totalRequests = PurchaseRequest::count();
        $approvedRequests = PurchaseRequest::where('status', 'PO')->count();
        $rejectedRequests = RequestLog::where('action', 'rejected')->distinct('purchaserequest_id')->count('purchaserequest_id');
        $recentLogs = RequestLog::with(['purchaseRequest', 'approvedBy'])->latest()->take(5)->get();
        $this->checkPendingRequests();
        return view('Super.dashboard', compact('totalRequests', 'approvedRequests', 'rejectedRequests', 'recentLogs'));
    }
    public function checkPendingRequests()
{
    $superUsers = \App\Models\User::where('role', 'super')->get();
    if ($superUsers->isEmpty()) {
        Log::warning('No super users found for pending PR notification');
        return;
    }

    // Cari purchase request yang statusnya masih PR (Pending Review)
    // dan belum dinotifikasi dalam 1 jam terakhir
    $pendingRequests = PurchaseRequest::where('status','PR')
        ->where(function($query) {
            $query->whereNull('last_notified_at')
                  ->orWhere('last_notified_at', '<', now()->subHour());
        })
        ->get();

    Log::info("Found {$pendingRequests->count()} pending purchase requests for notification");

    foreach ($pendingRequests as $purchaseRequest) {
        try {
            \Illuminate\Support\Facades\Notification::send($superUsers, new \App\Notifications\PendingPurchaseRequestNotification($purchaseRequest));

            // Update last_notified_at
            $purchaseRequest->update(['last_notified_at' => now()]);

            Log::info("✅ Pending PR notification sent for: {$purchaseRequest->nama_part} (ID: {$purchaseRequest->id})");
        } catch (\Exception $e) {
            Log::error("❌ Failed to send pending PR notification for ID: {$purchaseRequest->id} - " . $e->getMessage());
        }
    }

    return [
        'pending_count' => $pendingRequests->count(),
        'super_users' => $superUsers->count()
    ];
}
}
