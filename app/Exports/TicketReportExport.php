<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TicketReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $tickets;
    protected $totalParkir = 0;
    protected $totalLuarKota = 0;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function collection()
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            'CREATE DATE',
            'NO REQUEST',
            'TITLE',
            'REQUESTER',
            'CUSTOMER / RECIPIENT',
            'ADDRESS',
            'DESKRIPSI',
            'COURIER/EXPEDITION',
            'HANDLING USER',
            'SCHEDULE DATE',
            'DUE DATE',
            'FINISHED DATE',
            'PARKIR',
            'L.KOTA',
            'TOTAL BIAYA',
            'TICKET STATUS',
            'LAMPIRAN',
            'ADDITIONAL NOTES'
        ];
    }

    public function map($ticket): array
    {
        $detailRequest = is_array($ticket->detail_request) ? $ticket->detail_request : [];
        
        // Get costs from database fields
        $parkir = !empty($ticket->expenses_value) ? (float) $ticket->expenses_value : 0;
        $luarKota = !empty($ticket->other_costs) ? (float) $ticket->other_costs : 0;
        $totalBiaya = $parkir + $luarKota;
        
        $this->totalParkir += $parkir;
        $this->totalLuarKota += $luarKota;
        
        // Get courier/expedition info
        $courierInfo = isset($detailRequest['delivery_type']) ? $detailRequest['delivery_type'] : '';
        if (isset($detailRequest['courier_name'])) {
            $courierInfo .= ($courierInfo ? ' - ' : '') . $detailRequest['courier_name'];
        }
        
        $deskripsi = isset($detailRequest['package_description'])
            ? (is_array($detailRequest['package_description'])
                ? implode(', ', $detailRequest['package_description'])
                : $detailRequest['package_description'])
            : '';
        $lampiran = $ticket->receipt_comment ?? '';

        return [
            $ticket->created_at ? Date::dateTimeToExcel($ticket->created_at->startOfDay()) : null,
            $ticket->request_number ?? '',
            $ticket->title ?? '',
            $ticket->requester ? $ticket->requester->name : '',
            isset($detailRequest['recipient_name']) ? $detailRequest['recipient_name'] : 
                (isset($detailRequest['customer_name']) ? $detailRequest['customer_name'] : ''),
            isset($detailRequest['delivery_address']) ? $detailRequest['delivery_address'] : 
                (isset($detailRequest['address']) ? $detailRequest['address'] : ''),
            $deskripsi,
            $courierInfo,
            $ticket->handling_user ?? '',
            $ticket->schedule_date ? Date::dateTimeToExcel($ticket->schedule_date->startOfDay()) : null,
            $ticket->due_date ? Date::dateTimeToExcel($ticket->due_date->startOfDay()) : null,
            $ticket->finished_date ? Date::dateTimeToExcel($ticket->finished_date->startOfDay()) : null,
            $parkir,
            $luarKota,
            $totalBiaya,
            $ticket->status ? $ticket->status->name : '',
            $lampiran,
            $ticket->additional_note ?? ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Format number columns (M, N, O = Parkir, L.Kota, Total Biaya) - use format that shows 0
        $lastDataRow = $this->tickets->count() + 1;
        $sheet->getStyle('M2:O' . $lastDataRow)->getNumberFormat()
            ->setFormatCode('#,##0;-#,##0;0');

        // Format date columns (A, J, K, L = Create, Schedule, Due, Finished) with proper Excel date format
        $sheet->getStyle('A2:A' . $lastDataRow)->getNumberFormat()
            ->setFormatCode('mm/dd/yyyy');
        $sheet->getStyle('J2:L' . $lastDataRow)->getNumberFormat()
            ->setFormatCode('mm/dd/yyyy');

        // Add totals row at the bottom (values will be set in registerEvents)
        $lastRow = $this->tickets->count() + 2;
        $sheet->setCellValue('L' . $lastRow, 'TOTAL BIAYA');
        
        // Format totals row numbers - use format that shows 0
        $sheet->getStyle('M' . $lastRow . ':O' . $lastRow)->getNumberFormat()
            ->setFormatCode('#,##0;-#,##0;0');
        
        // Style the totals row
        $sheet->getStyle('A' . $lastRow . ':R' . $lastRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2']
            ]
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // CREATE DATE
            'B' => 20,  // NO REQUEST
            'C' => 30,  // TITLE
            'D' => 20,  // REQUESTER
            'E' => 25,  // CUSTOMER / RECIPIENT
            'F' => 35,  // ADDRESS
            'G' => 30,  // DESKRIPSI
            'H' => 25,  // COURIER/EXPEDITION
            'I' => 20,  // HANDLING USER
            'J' => 18,  // SCHEDULE DATE
            'K' => 18,  // DUE DATE
            'L' => 18,  // FINISHED DATE
            'M' => 15,  // PARKIR
            'N' => 15,  // L.KOTA
            'O' => 15,  // TOTAL BIAYA
            'P' => 20,  // TICKET STATUS
            'Q' => 40,  // LAMPIRAN
            'R' => 40,  // ADDITIONAL NOTES
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $this->tickets->count() + 1;
                
                // Ensure Parkir and Luar Kota cells display 0 when empty (don't touch Total Biaya as it's calculated)
                for ($row = 2; $row <= $lastDataRow; $row++) {
                    $parkirCell = $sheet->getCell('M' . $row);
                    $luarKotaCell = $sheet->getCell('N' . $row);
                    
                    $parkirValue = $parkirCell->getValue();
                    $luarKotaValue = $luarKotaCell->getValue();
                    
                    // If parkir cell is empty/null, set it to 0
                    if ($parkirValue === null || $parkirValue === '') {
                        $parkirCell->setValueExplicit(0, DataType::TYPE_NUMERIC);
                    }
                    
                    // If luarKota cell is empty/null, set it to 0
                    if ($luarKotaValue === null || $luarKotaValue === '') {
                        $luarKotaCell->setValueExplicit(0, DataType::TYPE_NUMERIC);
                    }
                    
                    // Recalculate and set Total Biaya based on actual cell values
                    $parkir = $parkirCell->getValue() ?: 0;
                    $luarKota = $luarKotaCell->getValue() ?: 0;
                    $total = $parkir + $luarKota;
                    $sheet->getCell('O' . $row)->setValueExplicit($total, DataType::TYPE_NUMERIC);
                }
                
                // Set the totals row
                $lastRow = $this->tickets->count() + 2;
                $sheet->getCell('M' . $lastRow)->setValueExplicit($this->totalParkir, DataType::TYPE_NUMERIC);
                $sheet->getCell('N' . $lastRow)->setValueExplicit($this->totalLuarKota, DataType::TYPE_NUMERIC);
                $sheet->getCell('O' . $lastRow)->setValueExplicit($this->totalParkir + $this->totalLuarKota, DataType::TYPE_NUMERIC);
            },
        ];
    }
}
