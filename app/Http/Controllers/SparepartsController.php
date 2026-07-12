<?php

namespace App\Http\Controllers;

use App\Exports\SparepartExport;
use App\Models\Spareparts;
use App\Models\User;
use App\Notifications\SparepartCriticalNotification;
use App\Services\HashIdService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SparepartsController extends Controller
{
    public function getRouteKeyName()
    {
        return 'hashid';
    }

    protected function resolveHashid($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (! $id) {
            abort(404);
        }

        return Spareparts::findOrFail($id);
    }

    protected function resolveHashids($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (! $id) {
            abort(404);
        }

        return Spareparts::where('id', $id); // RETURN QUERY BUILDER!
    }

    protected function resolveHashidWithTrashed($hashid)
    {
        $id = app(HashIdService::class)->decode($hashid);
        if (! $id) {
            abort(404);
        }

        return Spareparts::withTrashed()->findOrFail($id);
    }

    protected function normalizeDate($date)
    {
        if (! $date || $date === '-' || $date === 'PART FROM PE') {
            return null;
        }
        $formats = ['d/m/Y', 'Y-m-d', 'm/d/Y', 'd-M-yy', 'd-M-y'];
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if ($d) {
                return $d->format('Y-m-d');
            }
        }
        Log::warning('Tanggal tidak dapat dikonversi: '.$date);

        return null;
    }

    protected function normalizeNumber($value)
    {
        if (! $value) {
            return 0;
        }

        return (float) str_replace(['Rp', 'rp', ',', ' '], '', $value);
    }

    public function index(Request $request)
    {
        $query = Spareparts::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_part', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('jumlah_baru', 'like', "%{$search}%")
                    ->orWhere('jumlah_bekas', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%")
                    ->orWhere('patokan_harga', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%")
                    ->orWhere('ruk_no', 'like', "%{$search}%")
                    ->orWhere('purchase_date', 'like', "%{$search}%")
                    ->orWhere('delivery_date', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('titik_pesanan', 'like', "%{$search}%")
                    ->orWhere('jumlah_pesanan', 'like', "%{$search}%")
                    ->orWhere('cek', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('qr_code', 'like', "%{$search}%");
            });
        }

        $spareparts = $query->paginate(10)->withQueryString();

        // Hanya di halaman utama saja
        if (! $request->has('search') && ! $request->ajax()) {
            $this->checkStock();
        }

        return view('spareparts.index', compact('spareparts'));
    }

    public function create()
    {
        return view('spareparts.create');
    }

    public function store(Request $request)
    {
        Log::debug('Entering SparepartController@store');
        $validated = $request->validate([
            'nama_part' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'merk' => 'required|string|max:255',
            'jumlah_baru' => 'required|numeric',
            'jumlah_bekas' => 'required|numeric',
            'supplier' => 'required|string|max:255',
            'patokan_harga' => 'required|numeric',
            'total' => 'required|numeric',
            'ruk_no' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'delivery_date' => 'required|date',
            'po_number' => 'required|string|max:255',
            'titik_pesanan' => 'required|string|max:255',
            'jumlah_pesanan' => 'required|numeric',
            'cek' => 'required|boolean',
            'pic' => 'required|string|max:255',
            'qr_code' => 'nullable|string',
        ]);

        Log::debug('Raw request data: ', $request->all());
        Log::debug('Validated data: ', $validated);

        $sparepart = Spareparts::create($validated);
        Log::debug('Created sparepart with ID: '.$sparepart->id);

        $this->generateQrCode($sparepart, $sparepart->ruk_no);
        Log::debug('Generated QR code for sparepart ID: '.$sparepart->id);

        Log::debug('Exiting SparepartController@store');

        return redirect()->route('spareparts.index')->with('success', 'Sparepart created successfully.');
    }

    public function update(Request $request, $hashid)
    {
        Log::debug('Entering SparepartController@update for hashid: '.$hashid);
        $sparepart = $this->resolveHashid($hashid);
        $requestData = $request->all();

        Log::debug('Raw request data: ', $requestData);

        $validated = $request->validate([
            'nama_part' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'merk' => 'required|string|max:255',
            'jumlah_baru' => 'required|numeric',
            'jumlah_bekas' => 'required|numeric',
            'supplier' => 'required|string|max:255',
            'patokan_harga' => 'required|numeric',
            'total' => 'required|numeric',
            'ruk_no' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'delivery_date' => 'required|date',
            'po_number' => 'required|string|max:255',
            'titik_pesanan' => 'required|string|max:255',
            'jumlah_pesanan' => 'required|numeric',
            'cek' => 'required|boolean',
            'pic' => 'required|string|max:255',
            'qr_code' => 'nullable|string',
        ]);

        Log::debug('Validated data before update: ', $validated);

        $sparepart->update($validated);
        Log::debug('Updated sparepart with hashid: '.$hashid);

        $this->generateQrCode($sparepart, $sparepart->ruk_no);
        Log::debug('Regenerated QR code for sparepart hashid: '.$hashid);

        Log::debug('Exiting SparepartController@update');

        return redirect()->route('spareparts.index')->with('success', 'Sparepart updated successfully.');
    }

    public function show($hashid)
    {
        $sparepart = $this->resolveHashid($hashid);

        return view('spareparts.show', compact('sparepart'));
    }

    public function edit($hashid)
    {
        $sparepart = $this->resolveHashid($hashid);

        return view('spareparts.edit', compact('sparepart'));
    }

    public function unduh(): BinaryFileResponse
    {
        $export = new SparepartExport;

        return Excel::download($export, 'spareparts.xlsx');
    }

    public function downloadPdf($hashid)
    {
        $sparepart = $this->resolveHashids($hashid)->firstOrFail();
        $pdf = Pdf::loadView('spareparts.kanban', compact('sparepart'))->setPaper('A4');

        return $pdf->download("Kanban-{$sparepart->ruk_no}.pdf");
    }

    public function checkStock()
    {
        $admins = User::where('role', 'admin')->get();
        if ($admins->isEmpty()) {
            return;
        }

        // 1. KIRIM NOTIFIKASI: Hanya SEKALI (jika last_notified_at NULL)
        $criticalSpareparts = Spareparts::whereColumn('jumlah_baru', '<=', 'titik_pesanan')
            ->whereNull('last_notified_at')
            ->get();

        $criticalSpareparts->each(function ($sparepart) use ($admins) {
            Notification::send($admins, new SparepartCriticalNotification($sparepart));
            $sparepart->update(['last_notified_at' => now()]);
        });

        // 2. HAPUS NOTIFIKASI: Jika stok sudah normal kembali
        $recoveredSpareparts = Spareparts::whereColumn('jumlah_baru', '>', 'titik_pesanan')
            ->whereNotNull('last_notified_at')
            ->get();

        $recoveredSpareparts->each(function ($sparepart) {
            DB::table('notifications')
                ->where('type', \App\Notifications\SparepartCriticalNotification::class)
                ->whereJsonContains('data->sparepart_id', $sparepart->id)
                ->delete();
            $sparepart->update(['last_notified_at' => null]);
        });

        return [
            'critical' => $criticalSpareparts->count(),
            'recovered' => $recoveredSpareparts->count(),
        ];
    }

    protected function generateQrCode(Spareparts $sparepart, string $location): void
    {
        $storagePath = 'public/qrcodes';
        Storage::makeDirectory($storagePath, 0755, true);

        $qrCodePath = 'qrcodes/sparepart_'.$sparepart->hashid.'_'.Str::slug($location).'.png';
        $fullPath = storage_path('app/public/'.$qrCodePath);

        try {
            $qrCode = new QrCode(
                data: route('login').'?spareparts_id='.$sparepart->hashid, // QR LANGSUNG KE FORM!
                encoding: new Encoding('UTF-8'),
                size: 300,
                margin: 10,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            );

            $writer = new PngWriter;
            $result = $writer->write($qrCode);
            $result->saveToFile($fullPath);

            $sparepart->update(['qr_code' => $qrCodePath]);

        } catch (\Exception $e) {
            Log::error('QR Generation failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function regenerateQrCode($hashid)
    {
        Log::debug('Entering SparepartController@regenerateQrCode for hashid: '.$hashid);
        $sparepart = $this->resolveHashid($hashid);
        $this->generateQrCode($sparepart, $sparepart->ruk_no);
        Log::debug('Regenerated QR code for sparepart hashid: '.$hashid);

        return redirect()->back()->with('success', 'QR code regenerated successfully.');
    }

    public function generateAllQrCodes()
    {
        $spareparts = Spareparts::all();

        foreach ($spareparts as $sparepart) {
            // 1. HAPUS QR LAMA DARI STORAGE
            if ($sparepart->qr_code && Storage::exists('public/'.$sparepart->qr_code)) {
                Storage::delete('public/'.$sparepart->qr_code);
            }

            // 2. REGENERATE QR BARU
            $this->generateQrCode($sparepart, $sparepart->ruk_no);
        }

        return redirect()->route('spareparts.index')
            ->with('success', 'Semua QR Code berhasil diperbarui!');
    }

    public function fixInvalidDates()
    {
        $defaultDate = '1970-01-01';
        $affectedRows = Spareparts::where('purchase_date', 'PART FROM PE')
            ->orWhere('delivery_date', 'PART FROM PE')
            ->update([
                'purchase_date' => $defaultDate,
                'delivery_date' => $defaultDate,
            ]);

        if ($affectedRows > 0) {
            Log::info("Fixed {$affectedRows} records with invalid dates.");

            return redirect()->route('spareparts.index')->with('success', "Fixed {$affectedRows} records with invalid dates.");
        }

        return redirect()->route('spareparts.index')->with('info', 'No invalid dates found to fix.');
    }

    public function destroy($hashid)
    {
        $sparepart = $this->resolveHashid($hashid);
        $sparepart->delete();

        return redirect()->route('spareparts.index')->with('success', 'Sparepart soft-deleted successfully.');
    }

    public function restore($hashid)
    {
        $sparepart = $this->resolveHashidWithTrashed($hashid);
        $sparepart->restore();

        return redirect()
            ->route('spareparts.trashed')
            ->with('success', 'Sparepart restored successfully.');
    }

    public function forceDelete($hashid)
    {
        $sparepart = $this->resolveHashidWithTrashed($hashid);

        if ($sparepart->qr_code && Storage::exists('public/'.$sparepart->qr_code)) {
            Storage::delete('public/'.$sparepart->qr_code);
        }

        $sparepart->forceDelete();

        return redirect()
            ->route('spareparts.trashed')
            ->with('success', 'Sparepart permanently deleted.');
    }

    public function trashed()
    {
        $trashedSpareparts = Spareparts::onlyTrashed()->paginate(10);

        return view('spareparts.trashed', compact('trashedSpareparts'));
    }
}
