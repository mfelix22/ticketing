<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Manifest - {{ $courierName }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .info {
            margin-bottom: 20px;
        }

        .info table {
            width: 100%;
        }

        .info td {
            padding: 5px;
        }

        .packages-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .packages-table th,
        .packages-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .packages-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .packages-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .packages-table .overdue {
            background-color: #ffe0e0;
            font-weight: bold;
        }

        .signature {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature table {
            width: 100%;
        }

        .signature td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            padding-top: 60px;
            border-top: 1px solid #000;
        }

        .priority-urgent {
            color: #dc3545;
            font-weight: bold;
        }

        .priority-high {
            color: #fd7e14;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 1cm;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>DELIVERY MANIFEST</h1>
        <p><strong>Handler: {{ $courierName }}</strong></p>
        <p>Date: {{ now()->format('l, F d, Y') }}</p>
        <p>Print Time: {{ now()->format('H:i') }}</p>
    </div>

    <!-- Info Section -->
    <div class="info">
        <table>
            <tr>
                <td style="width: 50%;"><strong>Total Packages:</strong> {{ $tickets->count() }}</td>
                <td style="width: 50%;"><strong>Overdue:</strong>
                    {{ $tickets->filter(fn($t) => $t->due_date && $t->due_date->isPast())->count() }}</td>
            </tr>
            <tr>
                <td><strong>Priority - Urgent:</strong>
                    {{ $tickets->filter(fn($t) => $t->priority && $t->priority->name === 'Urgent')->count() }}</td>
                <td><strong>Priority - High:</strong>
                    {{ $tickets->filter(fn($t) => $t->priority && $t->priority->name === 'High')->count() }}</td>
            </tr>
        </table>
    </div>

    <!-- Packages Table -->
    <table class="packages-table">
        <thead>
            <tr>
                <th style="width: 3%;">No.</th>
                <th style="width: 7%;">Ticket ID</th>
                <th style="width: 12%;">Package Description</th>
                <th style="width: 8%;">Requester</th>
                <th style="width: 7%;">Department</th>
                <th style="width: 10%;">Recipient</th>
                <th style="width: 15%;">Address</th>
                <th style="width: 5%;">Priority</th>
                <th style="width: 7%;">Schedule Date</th>
                <th style="width: 7%;">Due Date</th>
                <th style="width: 6%;">Status</th>
                <th style="width: 13%;">Additional Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets->sortBy('due_date') as $ticket)
                @php
                    $isOverdue = $ticket->due_date && $ticket->due_date->isPast();
                    $priorityClass = match ($ticket->priority->name ?? 'Medium') {
                        'Urgent' => 'priority-urgent',
                        'High' => 'priority-high',
                        default => '',
                    };
                    // Get latest comment for additional notes
                    $latestComment = $ticket->comments->sortByDesc('created_at')->first();
                    $additionalNotes = '';
                    if (is_array($ticket->detail_request) && isset($ticket->detail_request['additional_notes'])) {
                        $additionalNotes = $ticket->detail_request['additional_notes'];
                    }
                    
                    // Get recipient and address
                    $detailRequest = is_array($ticket->detail_request) ? $ticket->detail_request : [];
                    $recipient = isset($detailRequest['recipient_name'])
                        ? $detailRequest['recipient_name']
                        : (isset($detailRequest['customer_name'])
                            ? $detailRequest['customer_name']
                            : '-');
                    $address = isset($detailRequest['delivery_address'])
                        ? $detailRequest['delivery_address']
                        : (isset($detailRequest['address'])
                            ? $detailRequest['address']
                            : '-');
                @endphp
                <tr class="{{ $isOverdue ? 'overdue' : '' }}">
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $ticket->request_number }}</strong></td>
                    <td>
                        {{ $ticket->title }}
                        @if ($isOverdue)
                            <br><small style="color: #dc3545;">⚠️ OVERDUE</small>
                        @endif
                    </td>
                    <td>{{ $ticket->requester->name ?? '-' }}</td>
                    <td>{{ $ticket->department->name ?? '-' }}</td>
                    <td style="font-size: 10px;">{{ $recipient }}</td>
                    <td style="font-size: 9px;">{{ $address }}</td>
                    <td class="{{ $priorityClass }}">
                        {{ $ticket->priority->name ?? 'Medium' }}
                    </td>
                    <td>
                        @if ($ticket->schedule_date)
                            {{ $ticket->schedule_date->format('M d') }}<br>
                            <small>{{ $ticket->schedule_date->format('H:i') }}</small>
                        @else
                            <span style="color: #6c757d;">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($ticket->due_date)
                            {{ $ticket->due_date->format('M d') }}<br>
                            <small>{{ $ticket->due_date->format('H:i') }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $ticket->status->name ?? 'Open' }}</td>
                    <td style="font-size: 10px;">
                        {{ $additionalNotes ?: '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Additional Information from Detail Request -->
    @if ($tickets->first() && is_array($tickets->first()->detail_request))
        <div style="margin-top: 20px; font-size: 11px;">
            <strong>Note:</strong> Check ticket details for specific delivery addresses and contact information.
        </div>
    @endif

    <!-- Signature Section -->
    <div class="signature">
        <table>
            <tr>
                <td>
                    <strong>Prepared By</strong><br><br><br><br>
                    _________________<br>
                    PIC / Admin
                </td>
                <td>
                    <strong>Received By (Handler)</strong><br><br><br><br>
                    _________________<br>
                    {{ $courierName }}
                </td>
                <td>
                    <strong>Verified By</strong><br><br><br><br>
                    _________________<br>
                    Supervisor
                </td>
            </tr>
        </table>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()"
            style="padding: 10px 30px; font-size: 14px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 5px;">
            🖨️ Print Manifest
        </button>
        <button onclick="window.close()"
            style="padding: 10px 30px; font-size: 14px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            Close
        </button>
    </div>

    <script>
        // Auto-print when page loads (optional - can be removed if not desired)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>
