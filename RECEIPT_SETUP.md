# Receipt PDF Generation Setup

This document explains the receipt PDF generation feature that was implemented.

## Overview

The system now automatically generates a PDF receipt when a PIC/Approver approves a ticket. The receipt can also be reprinted anytime after approval.

## Features Implemented

### 1. **Auto-Generation on Approval**
- When an approver clicks "Submit Receipt" and approves (is_continued=1)
- PDF receipt is automatically generated
- PDF is saved to `storage/app/public/receipts/` 
- A database record is created in `request_attachments` table
- Success message confirms PDF generation

### 2. **Print Receipt Button**
- Appears on ticket show page after receipt is submitted
- Available in two locations:
  - In the "Request Information" card header (for Delivery/Courier tickets)
  - As a dedicated "Receipt Document" card (for all ticket types)
- Opens PDF in new tab for printing/downloading
- Can be used unlimited times for reprints

### 3. **Receipt Template**
The PDF includes:
- Company header and receipt number
- Ticket and requester information
- Request details (title, type, priority, dates)
- Delivery information (recipient, address, phone, package description)
- Approval information (PIC, handler/courier, schedule date, receipt comment)
- Signature boxes (PIC, Courier, Recipient)
- Barcode/tracking number
- Professional formatting optimized for printing

## Installation Steps

### 1. Install PDF Package

Run this command to install the dompdf package:

```bash
cd /var/www/html/ticketing
composer require barryvdh/laravel-dompdf
```

### 2. Publish Config (Optional)

If you need to customize PDF settings:

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 3. Create Storage Symlink

Ensure the storage symlink exists so generated PDFs are accessible:

```bash
php artisan storage:link
```

### 4. Set Permissions

Make sure the storage directory is writable:

```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

## Files Created/Modified

### New Files:
- `resources/views/receipts/delivery-receipt.blade.php` - Receipt PDF template

### Modified Files:
- `app/Http/Controllers/TicketController.php`
  - Added `use Barryvdh\DomPDF\Facade\Pdf;`
  - Added `generateReceiptPDF()` private method
  - Added `printReceipt()` public method
  - Modified `receipt()` method to auto-generate PDF on approval
  
- `routes/web.php`
  - Added route: `tickets/{ticket}/print-receipt`

- `resources/views/tickets/show.blade.php`
  - Added "Print Receipt" button in Request Information card header
  - Added "Receipt Document" card section

## Usage

### For Approvers/PICs:

1. **Approving a Ticket:**
   - Navigate to ticket detail page
   - Fill out the "Receipt & Approval" form
   - Select "Yes - Approve & Continue"
   - Click "Submit Receipt"
   - PDF will be auto-generated and saved
   - Success message will confirm generation

2. **Printing/Reprinting Receipt:**
   - After approval, the "Print Receipt" button appears
   - Click "Print / Download Receipt PDF"
   - PDF opens in new tab
   - Use browser print function (Ctrl+P) or download

### For Couriers:

1. Receipt PDF will be available in ticket attachments
2. Can be printed directly from ticket page
3. Physical copy should be:
   - Signed by PIC
   - Signed by courier on pickup
   - Signed by recipient on delivery

## Troubleshooting

### PDF Generation Fails

If you see error message about PDF generation:

1. Check composer package is installed:
```bash
composer show barryvdh/laravel-dompdf
```

2. Check storage permissions:
```bash
ls -la storage/app/public/
```

3. Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### PDF Not Showing

1. Verify storage symlink:
```bash
ls -la public/storage
```

2. If missing, create it:
```bash
php artisan storage:link
```

### Permission Issues

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Customization

### Modify Receipt Template

Edit `resources/views/receipts/delivery-receipt.blade.php`:
- Change header/footer
- Adjust styling
- Add/remove fields
- Modify signature boxes

### Change PDF Settings

Edit `config/dompdf.php` (after publishing):
- Paper size (default: A4)
- Orientation (default: portrait)
- Font settings
- Image handling

### Add Company Logo

1. Place logo in `public/images/logo.png`
2. Update receipt template header:
```blade
<div class="header">
    <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-height: 60px;">
    <h1>Delivery Receipt</h1>
</div>
```

## Security Notes

- Only Approvers, PICs, and Super Admins can view/print receipts
- PDF generation is logged for audit trail
- Receipts are stored with ticket attachments
- Access controlled by existing ticket permissions

## Future Enhancements (Optional)

Ideas for future improvements:
- Email receipt to courier/recipient
- Add QR code for mobile scanning
- Thermal printer format option
- Batch receipt printing
- Digital signature integration
- SMS notification with receipt link
