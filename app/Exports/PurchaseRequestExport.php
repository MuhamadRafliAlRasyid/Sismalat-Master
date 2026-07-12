<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
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
use Carbon\Carbon;

class PurchaseRequestExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    protected $rows;

    public function collection()
    {
        return PurchaseRequest::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Part',
            'Part Number',
            'Link Website',
            'Waktu Request',
            'Quantity',
            'Satuan',
            'Masa Delivery',
            'Keterangan',
            'PIC',
            'Quotation Lead Time',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat'
        ];
    }



    public function map($row): array
    {
        return [
            $row->id,
            $row->nama_part ?? 'N/A',
            $row->part_number ?? 'N/A',
            $row->link_website ?? 'N/A',
            $row->waktu_request ? Carbon::parse($row->waktu_request)->format('d-M-y') : 'N/A',
            $row->quantity ?? 0,
            $row->satuan ?? 'N/A',
            $row->mas_deliver ? Carbon::parse($row->mas_deliver)->format('d-M-y') : 'N/A',
            $row->untuk_apa ?? 'N/A',
            $row->pic ?? 'N/A',
            $row->quotation_lead_time ?? 'N/A',
            $row->status ?? 'N/A',
            $row->user->name ?? 'Unknown',
            $row->created_at ? Carbon::parse($row->created_at)->format('d-M-y H:i') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2ECC71']], // Hijau untuk kontras
                'alignment' => ['horizontal' => 'center'],
            ],
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
                $sheet->getColumnDimension('C')->setWidth(20); // PART NUMBER
                $sheet->getColumnDimension('D')->setWidth(30); // LINK WEBSITE
                $sheet->getColumnDimension('E')->setWidth(15); // WAKTU REQUEST
                $sheet->getColumnDimension('F')->setWidth(10); // QUANTITY
                $sheet->getColumnDimension('G')->setWidth(10); // SATUAN
                $sheet->getColumnDimension('H')->setWidth(15); // MAS DELIVER
                $sheet->getColumnDimension('I')->setWidth(25); // UNTUK APA
                $sheet->getColumnDimension('J')->setWidth(15); // PIC
                $sheet->getColumnDimension('K')->setWidth(20); // QUOTATION LEAD TIME
                $sheet->getColumnDimension('L')->setWidth(10); // STATUS
                $sheet->getColumnDimension('M')->setWidth(15); // DIBUAT OLEH
                $sheet->getColumnDimension('N')->setWidth(15); // TANGGAL DIBUAT

                // Sesuaikan tinggi baris untuk kenyamanan membaca
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                // Tambahkan filter otomatis
                $highestColumn = $sheet->getHighestColumn();
                $sheet->setAutoFilter('A1:' . $highestColumn . $highestRow);

                // Freeze header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
