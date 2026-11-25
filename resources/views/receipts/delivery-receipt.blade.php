<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima - {{ $ticket->request_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            /* font-family: 'Courier New', 'Courier', monospace; */
            font-family: 'Arial', sans-serif;
            background: white;
            margin: 0;
            padding: 0 8mm 0 8mm;
            font-size: 10px;
            font-weight: normal;
        }

        .receipt-container {
            width: 100%;
            padding: 0 3mm;
        }

        .page-break {
            page-break-before: always;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding: 2mm 0 2mm 0;
            background: white;
            width: 100%;
            margin-bottom: 3px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 2mm;
            font-size: 9px;
            font-weight: bold;
            background: white;
        }

        .logo-section {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 0;
        }

        .logo-section img {
            height: 35px;
            width: auto;
            max-width: 60px;
            object-fit: contain;
        }

        .company-header {
            flex-grow: 1;
        }

        .company-header .company-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 1px;
        }

        .company-header .company-info {
            font-size: 11px;
            line-height: 1.2;
            font-weight: bold;
            color: #000;
        }

        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding: 8px 0;
            border-bottom: 1px solid #999;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .form-section {
            margin-bottom: 8px;
            padding: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 8px;
            margin-bottom: 8px;
            align-items: start;
        }

        .form-label {
            /* font-weight: bold; */
            font-size: 11px;
            text-transform: uppercase;
            color: #000;
            padding-top: 2px;
            letter-spacing: 0.5px;
        }

        .form-content {
            font-size: 10px;
            line-height: 1.5;
            /* font-weight: bold; */
        }

        .form-content-bold {
            font-weight: bold;
        }

        .form-content-italic {
            font-style: italic;
            color: #666;
        }

        .info-box {
            border: 1px solid #fff;
            /* padding: 10px; */
            padding-left: 5px;
            background: #ffffff;
            margin-bottom: 10px;
            /* font-weight: bold; */
            font-size: 10px;
            line-height: 1.4;
        }

        .lampiran-section {
            /* margin-top: 5px; */
            margin-bottom: 5px;
        }

        .lampiran-title {
            /* font-weight: bold; */
            font-size: 11px;
            text-transform: uppercase;
            padding-bottom: 0px;
            border-bottom: 1px solid #ffffff;
            margin-bottom: 2px;
            color: #000;
            letter-spacing: 0.5px;
        }

        .lampiran-item {
            padding: 5px;
            border: 1px solid #fff;
            background: #ffffff;
            margin-bottom: 5px;
            font-size: 10px;
            /* font-weight: bold; */
            line-height: 1.4;
        }

        .lampiran-item .note {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
            border-top: 1px solid #e0e0e0;
            padding-top: 3px;
        }

        .signature-section {
            width: 100%;
            /* margin-top: 20px; */
            page-break-inside: avoid;
        }

        .signature-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-section td {
            width: 33.33%;
            text-align: center;
            padding: 0 8px;
            vertical-align: top;
        }

        .signature-box .label {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            display: block;
        }

        .signature-box .line {
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
            display: block;
            margin-bottom: 8px;
        }

        .signature-box .name {
            font-size: 10px;
            color: #000;
            margin-top: 0;
            font-weight: bold;
            line-height: 1.2;
            display: block;
        }

        .footer-note {
            margin-top: 30px;
            font-size: 11px;
            color: #000;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
            z-index: 1000;
        }

        .print-btn:hover {
            background: #0056b3;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    {{-- <button onclick="window.print()" class="print-btn">
        <i class="fas fa-print"></i> Print Receipt
    </button> --}}

    <!-- Header provided by mPDF -->

    <div class="receipt-container">
        <!-- TANDA TERIMA Title -->
        <div class="title">TANDA TERIMA</div>

        <!-- Telah terima dan Oleh Section -->
        <div class="form-section">

            <div class="form-row">
                <div class="form-label">Telah terima dari : PT SURYA DARMA PERKASA</div>
                {{-- <div class="form-content">
                    <div class="form-content-bold">PT SURYA DARMA PERKASA</div>
                </div> --}}
            </div>

            <div class="form-row">
                <div class="form-label">Oleh :
                    {{-- <strong>{{ $ticket->detail_request['recipient_name'] ?? 'RECIPIENT NAME' }}</strong> --}}
                    {{ $ticket->detail_request['recipient_name'] ?? 'RECIPIENT NAME' }}
                </div>
                <div class="form-content">
                    {{-- <div class="form-content-bold">{{ $ticket->detail_request['recipient_name'] ?? 'RECIPIENT NAME' }}
                    </div> --}}
                    <div>
                        {{ $ticket->detail_request['delivery_address'] ?? 'DELIVERY ADDRESS' }}</div>
                    <div>Telp. {{ $ticket->detail_request['phone_number'] ?? '' }}</div>
                </div>
            </div>

        </div>

        <!-- Package Description Section (Invoice No) -->
        <div class="lampiran-section">
            <div class="lampiran-title">Deskripsi Paket / Invoice No</div>
            <div class="info-box">
                <div>{{ $ticket->detail_request['package_description'] }}</div>
            </div>
        </div>

        <!-- Lampiran Section (From Receipt Comment) -->
        @if ($ticket->receipt_comment)
            <div class="lampiran-section">
                <div class="lampiran-title">Lampiran</div>
                <div class="lampiran-item">
                    {{ $ticket->receipt_comment }}
                    {{-- <div class="note">
                        <strong>By:</strong> {{ $ticket->receiptUser->name ?? 'System' }} -
                        {{ $ticket->receipt_date ? $ticket->receipt_date->format('M d, Y H:i') : 'N/A' }}
                    </div> --}}
                </div>
            </div>
        @endif

        <!-- Signatures -->
        <div class="signature-section">
            <table>
                <tr>
                    <td>
                        <div class="signature-box">
                            <div style="font-size: 9.5px; margin-bottom: 5px; font-weight: normal; visibility: hidden;">
                                &nbsp;</div>
                            <div class="label" style="font-size: 10.5px; text-transform: uppercase;">Yang Menyerahkan
                            </div>
                            <br><br><br><br><br>
                            <hr style="border: 0; border-top: 2px solid #000; height: 0; margin: 0 0 8px 0;">
                            <div class="name" style="font-size: 10.5px; line-height: 1.2;">
                                {{ $ticket->assignedTo->name ?? 'PIC' }} /
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-box">
                            <div style="font-size: 9.5px; margin-bottom: 5px; font-weight: normal; visibility: hidden;">
                                &nbsp;</div>
                            <div class="label" style="font-size: 10.5px; text-transform: uppercase;">Kurir</div>
                            <br><br><br><br><br>
                            <hr style="border: 0; border-top: 2px solid #000; height: 0; margin: 0 0 8px 0;">
                            <div class="name" style="font-size: 10.5px; line-height: 1.2;">
                                {{ $ticket->handling_user ?? 'Handler' }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-box">
                            <div style="font-size: 9.5px; font-weight: normal;">
                                {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}
                            </div>
                            <div class="label" style="font-size: 10.5px; text-transform: uppercase;">
                                Yang Menerima</div>
                            <br><br><br><br><br>
                            <hr style="border: 0; border-top: 2px solid #000; height: 0; margin: 0 0 8px 0;">
                            <div class="name" style="font-size: 10.5px; line-height: 1.2;">Tanda
                                Tangan dan Nama Jelas</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Note -->
        {{-- <div class="footer-note">
            <p>NOTE: MOHON TANDA TERIMA DAPAT DI E-MAIL KE finance@hartonrentcar.com, TERIMA KASIH</p>
        </div> --}}
    </div>

    <!-- Footer provided by mPDF -->
