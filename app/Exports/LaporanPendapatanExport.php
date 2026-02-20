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

class LaporanPendapatanExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings
{
    protected array $rows;

    protected ?string $title;

    public function __construct(iterable $mappedRows, ?string $title = null)
    {
        $this->rows = collect($mappedRows)->values()->all();
        $this->title = $title;
    }

    public function collection()
    {
        // map to: No, Tanggal, Jumlah, Metode, Status, Total Bayar
        $data = [];
        foreach ($this->rows as $i => $r) {
            $data[] = [
                $i + 1,
                $r['formatted_tanggal'] ?? '-',
                $r['formatted_jumlah'] ?? '-',
                $r['formatted_metode'] ?? '-',
                $r['formatted_status'] ?? '-',
                $r['formatted_total'] ?? '-',
            ];
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Jumlah', 'Metode', 'Status', 'Total Bayar'];
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
                    // merge sampai F (6 kolom)
                    $event->sheet->mergeCells('A1:F1');
                    $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                }
            },

            // Styling seperti PelangganExport
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn(); // mis. 'F'

                if ($lastRow < 1) {
                    return;
                }

                // Baris heading tergantung title
                $headerRow = $this->title ? 3 : 1;

                // Range header + full range (data)
                $headerRange = 'A'.$headerRow.':'.$lastColumn.$headerRow;
                $fullRange = 'A'.$headerRow.':'.$lastColumn.$lastRow;

                // Header: pink muda + bold + center + border
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
