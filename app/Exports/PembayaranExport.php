<?php

namespace App\Exports;

use App\Models\Pembayaran;
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

class PembayaranExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
{
    public function collection()
    {
        // Mengambil data dari tabel pembayaran termasuk order_id
        return Pembayaran::query()
            ->leftJoin('metodepembayaran as mp', 'mp.id_metodePembayaran', '=', 'pembayaran.id_metodePembayaran')
            ->select('pembayaran.*', 'mp.nama as nama_metode')  // Tidak perlu join lagi dengan tabel lain
            ->orderByDesc('pembayaran.id_pembayaran')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Pembayaran',
            'ID Reservasi',
            'Jumlah',
            'Diskon',
            'Total Bersih',
            'Status',
            'Metode Pembayaran',
            'Tanggal Pembayaran (Y-m-d H:i:s)',
            'Order ID',  // Menambahkan kolom Order ID
        ];
    }

    public function map($p): array
    {
        $total = ((float) $p->jumlah) - ((float) ($p->diskon_applied ?? 0));

        return [
            $p->id_pembayaran,
            $p->id_reservasi,
            number_format((float) $p->jumlah, 2, '.', ''),
            number_format((float) ($p->diskon_applied ?? 0), 2, '.', ''),
            number_format($total, 2, '.', ''),
            $p->status_pembayaran,
            $p->nama_metode ?? ('ID#'.$p->id_metodePembayaran),
            optional($p->tanggal_pembayaran)->format('Y-m-d H:i:s'),
            $p->order_id,  // Menambahkan Order ID dari tabel pembayaran
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                $header = 'A1:'.$lastColumn.'1';
                $full = 'A1:'.$lastColumn.$lastRow;

                // Header pink muda + bold + center + border
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

                // Border semua sel + vertical align
                $sheet->getStyle($full)->applyFromArray([
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

                // Freeze header + AutoFilter
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($header);

                // AutoSize semua kolom
                $lastColIndex = Coordinate::columnIndexFromString($lastColumn);
                for ($col = 1; $col <= $lastColIndex; $col++) {
                    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                }

                // Format angka (C:E) dan tanggal (H)
                if ($lastRow >= 2) {
                    $sheet->getStyle("C2:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
                }
            },
        ];
    }
}
