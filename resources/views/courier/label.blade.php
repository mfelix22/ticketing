<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Label - {{ $ticket->request_number }}</title>
    <style>
        /* 101.6mm x 152.4mm label size (4" x 6" - XP-420B standard) */
        @page {
            size: 101.6mm 152.4mm;
            margin: 0mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: white;
            margin: 0;
            padding: 0;
        }

        .label-container {
            width: 101.6mm;
            height: 152.4mm;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 6mm;
            page-break-after: always;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .request-number {
            font-size: 24px;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 12px;
            text-align: center;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .section {
            margin-bottom: 15px;
            padding: 15px;
            border: 2px solid #333;
            background: #f9f9f9;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .section-content {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            word-wrap: break-word;
            line-height: 1.6;
            min-height: 25px;
        }

        .address-section .section-content {
            font-size: 16px;
            line-height: 1.6;
            min-height: 50px;
            max-height: 40mm;
            /* tighter cap so phone box stays safe */
            overflow: hidden;
            /* hide overflow instead of pushing layout */
            white-space: normal;
            /* wrap normally */
            word-break: break-word;
            /* break long tokens */
            hyphens: auto;
            /* nicer word breaks */
            display: -webkit-box;
            /* enable line clamp */
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 10;
            /* clamp to ~10 lines */
        }

        .notes-section {
            flex-grow: 1;
            margin-top: 6px;
            padding: 5px;
            border: 1px solid #333;
            background: #fff;
            min-height: 30mm;
        }

        .notes-section .section-title {
            font-size: 8px;
            margin-bottom: 3px;
        }

        .notes-section .section-content {
            font-size: 9px;
            font-weight: normal;
            line-height: 1.3;
        }

        .signature-area {
            margin-top: 6px;
            padding: 5px;
            border: 1px solid #333;
            background: #f9f9f9;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 4px;
        }

        .signature-box {
            text-align: center;
            padding: 3px;
        }

        .signature-box .label {
            font-size: 7px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .signature-box .line {
            border-top: 1px solid #000;
            margin: 15px 5px 2px 5px;
        }

        .signature-box .date-line {
            font-size: 7px;
            color: #666;
            margin-top: 2px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .label-container {
                border: 2px solid #000;
                width: 101.6mm;
                height: 152.4mm;
                margin: 0;
                padding: 6mm;
                page-break-after: always;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <button onclick="window.print()" class="print-btn no-print">
        <i class="fas fa-print"></i> Print Label
    </button>

    <div class="label-container">
        <!-- Header -->
        <div class="header">
            <h1>📦 DELIVERY LABEL</h1>
        </div>

        <!-- Request Number -->
        <div class="request-number">
            {{ $ticket->request_number }}
        </div>

        <!-- Delivery Type (if available) -->
        {{-- @if (isset($ticket->detail_request['delivery_type']) && !empty($ticket->detail_request['delivery_type']))
            <div class="section">
                <div class="section-title">Delivery Type</div>
                <div class="section-content">
                    {{ $ticket->detail_request['delivery_type'] }}
                </div>
            </div>
        @endif --}}

        <!-- Recipient Name -->
        <div class="section">
            <div class="section-title">Recipient Name</div>
            <div class="section-content">
                {{ $ticket->detail_request['recipient_name'] ?? 'N/A' }}
            </div>
        </div>

        <!-- Delivery Address -->
        <div class="section address-section">
            <div class="section-title">Delivery Address</div>
            <div class="section-content">
                {{ $ticket->detail_request['delivery_address'] ?? 'N/A' }}
            </div>
        </div>

        <!-- Phone Number -->
        <div class="section">
            <div class="section-title">Phone Number</div>
            <div class="section-content">
                {{ $ticket->detail_request['phone_number'] ?? 'N/A' }}
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>
