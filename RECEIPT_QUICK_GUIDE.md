# Receipt PDF Quick Reference

## ✅ Installation Complete

The receipt PDF generation system is now fully installed and ready to use!

### What Was Installed:
- **barryvdh/laravel-dompdf** package (v3.1.1)
- Receipt PDF template
- Auto-generation on approval
- Print/reprint buttons
- Storage directories created

---

## 🚀 How to Use

### For Approvers/PICs:

#### **1. Approving a Ticket**
1. Go to ticket detail page
2. Scroll to "Receipt & Approval" section
3. Fill in:
   - Handler/Courier name
   - Schedule date
   - Receipt comment
4. Select "Yes - Approve & Continue"
5. Click "Submit Receipt"
6. ✅ **PDF auto-generates and saves!**

#### **2. Printing Receipt**
After approval, two buttons appear:

**Option A:** In "Request Information" card (Delivery tickets)
- Click "Print Receipt" button (blue, PDF icon)

**Option B:** In "Receipt Document" card (All tickets)
- Click "Print / Download Receipt PDF" button
- Opens in new tab
- Use browser Ctrl+P to print

---

## 📄 Receipt Contents

The PDF includes:
```
┌─────────────────────────────────────┐
│  DELIVERY RECEIPT                   │
│  Receipt No: RCP-2025-0001          │
├─────────────────────────────────────┤
│  REQUESTER INFO                     │
│  - Name, Email, Department          │
├─────────────────────────────────────┤
│  REQUEST DETAILS                    │
│  - Title, Type, Priority, Dates     │
├─────────────────────────────────────┤
│  DELIVERY INFO                      │
│  - Recipient Name                   │
│  - Phone Number                     │
│  - Delivery Address                 │
│  - Package Description              │
├─────────────────────────────────────┤
│  APPROVAL INFO                      │
│  - Approved By (PIC)                │
│  - Handler/Courier                  │
│  - Schedule & Due Dates             │
│  - Receipt Comment                  │
├─────────────────────────────────────┤
│  SIGNATURES                         │
│  [ PIC ]  [ Courier ]  [ Recipient ]│
├─────────────────────────────────────┤
│  TRACKING: REQ-2025-C-0066          │
└─────────────────────────────────────┘
```

---

## 🔧 Technical Details

### Files Created:
- `resources/views/receipts/delivery-receipt.blade.php`
- `storage/app/public/receipts/` directory

### Files Modified:
- `app/Http/Controllers/TicketController.php`
  - `generateReceiptPDF()` method
  - `printReceipt()` method
  - `receipt()` method updated
- `routes/web.php`
  - Route: `GET /tickets/{ticket}/print-receipt`
- `resources/views/tickets/show.blade.php`
  - Added print buttons

### Storage Location:
- PDFs saved to: `storage/app/public/receipts/`
- Accessible via: `public/storage/receipts/`
- Also saved to `request_attachments` table

---

## ✨ Features

✅ **Auto-Generation**: PDF created automatically on approval  
✅ **Reprint Anytime**: Click button to reprint whenever needed  
✅ **Professional Format**: Clean A4 layout with company branding  
✅ **Signature Boxes**: Ready for PIC, Courier, and Recipient signatures  
✅ **Tracking Number**: Includes barcode/tracking reference  
✅ **Audit Trail**: Saved to attachments for record keeping  
✅ **Browser Print**: Opens in new tab for easy printing  

---

## 📝 Workflow Example

1. **Rena creates** delivery request REQ-2025-C-0066
2. **Felix (PIC)** reviews and approves:
   - Assigns to courier "Kurniawan"
   - Sets schedule date
   - Adds receipt comment
3. **PDF auto-generates** → Saved to database
4. **Felix clicks** "Print Receipt"
5. **PDF opens** in browser
6. **Felix prints** receipt
7. **Physical copy** goes to Kurniawan (courier)
8. **Kurniawan signs** on pickup
9. **Recipient signs** on delivery
10. **Signed copy returned** for filing

---

## 🎯 Benefits

- **Paperless workflow** - Generate on-demand
- **No manual typing** - All data auto-filled
- **Professional appearance** - Standardized format
- **Audit trail** - Every receipt stored
- **Time saving** - One-click generation
- **Proof of delivery** - Signature boxes included

---

## 🆘 Troubleshooting

### PDF Not Generating?
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify storage permissions
3. Clear cache: `php artisan cache:clear`

### Button Not Showing?
- Button only appears after `receipt_date` is set
- Only visible to Approvers, PICs, and Super Admin

### Can't Print?
- Check browser popup blocker
- Try different browser
- Download PDF instead of printing directly

---

## 📞 Support

For issues or questions:
1. Check `storage/logs/laravel.log`
2. Verify `storage/app/public/receipts/` exists
3. Test with a sample ticket approval

---

**Ready to use!** Just approve a ticket and the PDF will generate automatically. 🎉
