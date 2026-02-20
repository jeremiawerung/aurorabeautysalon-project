<?php

namespace App\Exports;

use App\Models\Pelanggan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PelangganExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
{
    public function collection()
    {
        return Pelanggan::orderByDesc('id_pelanggan')->get();
    }

    public function headings(): array
    {
        return [
            'ID Pelanggan',
            'Nama',
            'Email',
            'Nomor Telepon',
            'Status',
            'Tanggal Daftar (Y-m-d)', // << disesuaikan
        ];
    }

    public function map($p): array
    {
        // Ambil tanggal_daftar (DATE). Jika null, fallback ke created_at.
        $rawDate = $p->tanggal_daftar ?? $p->created_at;

        // Normalisasi ke string Y-m-d tanpa error kalau string biasa
        $tgl = null;
        if ($rawDate) {
            try {
                $tgl = Carbon::parse($rawDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                // kalau gagal parse, tampilkan raw apa adanya
                $tgl = (string) $rawDate;
            }
        }

        return [
            $p->id_pelanggan,
            $p->nama,
            $p->email,
            $p->nomor_telepon,
            $p->status_pelanggan,
            $tgl,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn(); // mis. 'F'
                $fullRange = 'A1:'.$lastColumn.$lastRow;
                $header = 'A1:'.$lastColumn.'1';

                // Header: pink muda + bold + center + border
                $sheet->getStyle($header)->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4EC'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDDDDDD'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Border seluruh range + vertical align
                $sheet->getStyle($fullRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFDDDDDD'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => false,
                    ],
                ]);

                // Freeze header & AutoFilter
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($header);

                // AutoSize kolom
                $lastColIndex = Coordinate::columnIndexFromString($lastColumn);
                for ($c = 1; $c <= $lastColIndex; $c++) {
                    $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
                }

                // Format kolom tanggal (kolom F) menjadi yyyy-mm-dd
                // (Jika posisi kolom berubah, sesuaikan huruf kolomnya)
                if ($lastColIndex >= 6) {
                    $sheet->getStyle('F2:F'.$lastRow)
                        ->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                }
            },
        ];
    }
}
