<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanRerataExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings
{
    protected array $rows;

    protected ?string $title;

    public function __construct(iterable $rows, ?string $title = null)
    {
        $this->rows = collect($rows)->values()->all();
        $this->title = $title;
    }

    public function collection()
    {
        // map to: No, Waktu (Per Hari), Rerata Transaksi, Jumlah Transaksi
        $data = [];
        foreach ($this->rows as $i => $r) {
            $data[] = [
                $i + 1,
                $r['waktu'] ?? '-',
                $r['rerata'] ?? '-',
                $r['jumlah'] ?? 0,
            ];
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return ['No', 'Waktu (Per Hari)', 'Rerata Transaksi', 'Jumlah Transaksi'];
    }

    public function startCell(): string
    {
        return $this->title ? 'A3' : 'A1';
    }

    public function registerEvents(): array
    {
        return [
            // Judul di baris pertama (jika ada)
            BeforeSheet::class => function (BeforeSheet $event) {
                if ($this->title) {
                    $event->sheet->setCellValue('A1', $this->title);
                    $event->sheet->mergeCells('A1:D1');
                    $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                }
            },

            // Styling seperti PelangganExport
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn(); // mis. 'D'

                if ($lastRow < 1) {
                    return;
                }

                $headerRow = $this->title ? 3 : 1;
                $headerRange = 'A'.$headerRow.':'.$lastColumn.$headerRow;
                $fullRange = 'A'.$headerRow.':'.$lastColumn.$lastRow;

                // Header style
                $sheet->getStyle($headerRange)->applyFromArray([
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
                $sheet->getRowDimension($headerRow)->setRowHeight(22);

                // Border + align seluruh range
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
                $sheet->freezePane('A'.($headerRow + 1));
                $sheet->setAutoFilter($headerRange);

                // AutoSize kolom
                $lastColIndex = Coordinate::columnIndexFromString($lastColumn);
                for ($c = 1; $c <= $lastColIndex; $c++) {
                    $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
                }
            },
        ];
    }
}
