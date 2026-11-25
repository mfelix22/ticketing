<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ITTicketReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tickets;

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
            'No Ticket (ID)',
            'Title',
            'Requester',
            'Handler',
            'Priority',
            'Request Details',
            'Completion Note',
            'Status',
            'Ticket Created',
            'Ticket Approved',
            'Ticket Finished',
            'Duration (Hours)'
        ];
    }

    public function map($ticket): array
    {
        $duration = null;
        if ($ticket->created_at && $ticket->finished_date) {
            $duration = $ticket->finished_date->floatDiffInHours($ticket->created_at);
            $duration = number_format($duration, 2);
        } else {
            $duration = '-';
        }

        $detailRequest = $ticket->detail_request;
        $requestDetails = '-';
        if (is_array($detailRequest) && count($detailRequest) > 0) {
            $parts = [];
            foreach ($detailRequest as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $val = is_array($value) ? implode(', ', $value) : $value;
                $parts[] = "{$label}: {$val}";
            }
            $requestDetails = implode(' | ', $parts);
        } elseif (is_string($detailRequest) && !empty($detailRequest)) {
            $requestDetails = $detailRequest;
        }

        return [
            $ticket->request_number ?? '',
            $ticket->title ?? '',
            $ticket->requester ? $ticket->requester->name : '',
            $ticket->handling_user ?? '-',
            $ticket->priority ? $ticket->priority->name : '-',
            $requestDetails,
            $ticket->note ?? '-',
            $ticket->status ? $ticket->status->name : '-',
            $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-',
            $ticket->receipt_date ? $ticket->receipt_date->format('d/m/Y H:i') : ($ticket->acceptance_date ? $ticket->acceptance_date->format('d/m/Y H:i') : '-'),
            $ticket->finished_date ? $ticket->finished_date->format('d/m/Y H:i') : '-',
            $duration
        ];
    }
}
