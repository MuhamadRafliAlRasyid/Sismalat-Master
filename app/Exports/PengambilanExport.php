<?php

namespace App\Exports;

use App\Models\Pengambilan;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class PengambilanExport implements WithMultipleSheets
{
    protected $hashid;

    public function __construct($id = null)
    {
        $this->hashid = $id;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Arsip Pengambil (Putih dengan tulisan hitam)
        $sheets[] = new class($this->hashid) implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents {
            protected $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function collection()
            {
                Log::info('Collecting pengambilan data for export (Pengambil)');
                $query = Pengambilan::with(['user', 'bagian', 'sparepart']);
                if ($this->id) {
                    $query->where('id', $this->id);
                }
                $data = $query->get();
                Log::info('Collected ' . $data->count() . ' records');
                return $data;
            }

            public function headings(): array
            {
                return ['NO', 'Nama Mesin', 'No Kanban', 'Nama Pengambil', 'Quantity', 'Bagian', 'Tanggal', 'Alasan', 'Cek'];
            }

            public function map($row): array
            {
                return [
                    $row->id,
                    $row->sparepart->nama_part ?? 'N/A',
                    $row->sparepart->ruk_no ?? 'N/A',
                    $row->user->name ?? 'N/A',
                    $row->jumlah,
                    $row->bagian->nama ?? 'N/A',
                    $row->waktu_pengambilan ? \Carbon\Carbon::parse($row->waktu_pengambilan)->format('d-M-y') : 'N/A',
                    $row->keperluan ?? 'N/A',
                    $row->jumlah > 0 ? '〇' : '×', // Cek berdasarkan jumlah
                ];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                        'alignment' => ['horizontal' => 'center'],
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    ],
                    2 => [
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    ],
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getPageSetup()->setFitToWidth(1); // Sesuaikan untuk cetak
                        $sheet->getPageMargins()->setTop(0.5);
                        $sheet->getPageMargins()->setBottom(0.5);
                        $highestRow = $sheet->getHighestRow();
                        $highestColumn = $sheet->getHighestColumn();

                        // Auto-fit semua kolom
                        for ($col = 'A'; $col <= $highestColumn; $col++) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        // Tambahkan filter pada header
                        $sheet->setAutoFilter('A1:' . $highestColumn . $highestRow);

                        // Tambahkan instruksi cetak
                        $sheet->setCellValue('A' . ($highestRow + 2), 'Silakan cetak untuk arsip pengambil');
                    },
                ];
            }
        };

        // Sheet 2: Arsip Diambil (Merah dengan tulisan biru)
        $sheets[] = new class($this->hashid) implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents {
            protected $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function collection()
            {
                Log::info('Collecting pengambilan data for export (Diambil)');
                $query = Pengambilan::with(['user', 'bagian', 'sparepart']);
                if ($this->id) {
                    $query->where('id', $this->id);
                }
                $data = $query->get();
                Log::info('Collected ' . $data->count() . ' records');
                return $data;
            }

            public function headings(): array
            {
                return ['NO', 'Nama Mesin', 'No Kanban', 'Nama Pengambil', 'Quantity', 'Bagian', 'Tanggal', 'Alasan', 'Cek'];
            }

            public function map($row): array
            {
                return [
                    $row->id,
                    $row->sparepart->nama_part ?? 'N/A',
                    $row->sparepart->ruk_no ?? 'N/A',
                    $row->user->name ?? 'N/A',
                    $row->jumlah,
                    $row->bagian->nama ?? 'N/A',
                    $row->waktu_pengambilan ? \Carbon\Carbon::parse($row->waktu_pengambilan)->format('d-M-y') : 'N/A',
                    $row->keperluan ?? 'N/A',
                    $row->jumlah > 0 ? '〇' : '×',
                ];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => '0000FF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']],
                        'alignment' => ['horizontal' => 'center'],
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    ],
                    2 => [
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    ],
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getPageSetup()->setFitToWidth(1);
                        $sheet->getPageMargins()->setTop(0.5);
                        $sheet->getPageMargins()->setBottom(0.5);
                        $highestRow = $sheet->getHighestRow();
                        $highestColumn = $sheet->getHighestColumn();

                        // Auto-fit semua kolom
                        for ($col = 'A'; $col <= $highestColumn; $col++) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        // Tambahkan filter pada header
                        $sheet->setAutoFilter('A1:' . $highestColumn . $highestRow);

                        // Tambahkan instruksi cetak
                        $sheet->setCellValue('A' . ($highestRow + 2), 'Silakan cetak untuk arsip diambil');
                    },
                ];
            }
        };

        return $sheets;
    }
}
