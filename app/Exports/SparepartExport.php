<?php

namespace App\Exports;

use App\Models\Spareparts;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;

class SparepartExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithDrawings
{
    protected $rows;

    public function collection()
    {
        Log::info('Collecting spareparts data');
        $this->rows = Spareparts::all();
        Log::info('Collected ' . $this->rows->count() . ' records');
        return $this->rows;
    }

    public function headings(): array
    {
        Log::info('Defining export headings');
        return [
            'No',
            'NAMA PART',
            'MODEL',
            'MERK',
            'JUMLAH BARU',
            'JUMLAH BEKAS',
            'SUPPLIER',
            'PATOKAN HARGA',
            'TOTAL',
            'RUK No',
            'PURCHASE DATE',
            'DELIVERY DATE',
            'PO NUMBER',
            'TITIK PESANAN',
            'JUMLAH PESANAN',
            'CEK',
            'PIC',
            'QR CODE',
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->nama_part ?? 'N/A',
            $row->model ?? 'N/A',
            $row->merk ?? 'N/A',
            $row->jumlah_baru ?? 0,
            $row->jumlah_bekas ?? 0,
            $row->supplier ?? 'PART FROM PE',
            $row->patokan_harga ? 'Rp ' . number_format($row->patokan_harga, 2, ',', '.') : 'Rp 0.00',
            $row->total ? 'Rp ' . number_format($row->total, 2, ',', '.') : 'PART FROM PE',
            $row->ruk_no ?? '01-1',
            $row->purchase_date ? \Carbon\Carbon::parse($row->purchase_date)->format('d-M-y') : 'PART FROM PE',
            $row->delivery_date ? \Carbon\Carbon::parse($row->delivery_date)->format('d-M-y') : 'PART FROM PE',
            $row->po_number ?? 'PART FROM PE',
            $row->titik_pesanan ?? 0,
            $row->jumlah_pesanan ?? 0,
            $row->cek ? '〇' : '×',
            $row->pic ?? 'Jafar',
            '' // Kosong, nanti diganti dengan gambar QR
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3498DB']],
                'alignment' => ['horizontal' => 'center'],
            ],
            'P' => ['alignment' => ['horizontal' => 'center']], // Kolom CEK rata tengah
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Set manual width kolom agar lebih rapih
                $sheet->getColumnDimension('B')->setWidth(25); // NAMA PART
                $sheet->getColumnDimension('C')->setWidth(18); // MODEL
                $sheet->getColumnDimension('D')->setWidth(18); // MERK
                $sheet->getColumnDimension('E')->setWidth(15); // JUMLAH BARU
                $sheet->getColumnDimension('F')->setWidth(15); // JUMLAH BEKAS
                $sheet->getColumnDimension('G')->setWidth(25); // SUPPLIER
                $sheet->getColumnDimension('H')->setWidth(20); // PATOKAN HARGA
                $sheet->getColumnDimension('I')->setWidth(20); // TOTAL
                $sheet->getColumnDimension('J')->setWidth(15); // RUK No
                $sheet->getColumnDimension('K')->setWidth(20); // PURCHASE DATE
                $sheet->getColumnDimension('L')->setWidth(20); // DELIVERY DATE
                $sheet->getColumnDimension('M')->setWidth(20); // PO NUMBER

                // Kolom QR Code fixed width
                $sheet->getColumnDimension('R')->setWidth(18);

                // Sesuaikan tinggi baris agar QR tidak terpotong
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(60);
                }

                // Tambahkan filter otomatis
                $highestColumn = $sheet->getHighestColumn();
                $sheet->setAutoFilter('A1:' . $highestColumn . $highestRow);
            },
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $rowIndex = 2; // mulai dari baris ke-2 (karena baris 1 adalah heading)

        foreach ($this->rows as $row) {
            if ($row->qr_code && file_exists(storage_path('app/public/' . $row->qr_code))) {
                $drawing = new Drawing();
                $drawing->setName('QR Code');
                $drawing->setDescription('QR Code for ' . $row->nama_part);
                $drawing->setPath(storage_path('app/public/' . $row->qr_code));
                $drawing->setHeight(50); // QR tinggi 50px biar pas
                $drawing->setCoordinates('R' . $rowIndex);

                // Biar QR code center
                $drawing->setOffsetX(25);
                $drawing->setOffsetY(5);

                $drawings[] = $drawing;
            }
            $rowIndex++;
        }

        return $drawings;
    }
}
