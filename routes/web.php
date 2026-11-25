<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\RequestTypeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // API route for fetching request type fields (returns empty array if no fields)
    Route::get('/api/request-types/{requestType}/fields', function (\App\Models\RequestType $requestType) {
        try {
            $fields = $requestType->fields ?? [];
            return response()->json($fields);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    })->name('api.request-types.fields');

    // API route for fetching customers (for BC department + Delivery only)
    Route::get('/api/customers', function () {
        try {
            $customers = \App\Models\Customer::active()
                ->orderBy('name')
                ->get(['id', 'name', 'pic_name', 'phone', 'address', 'code']);
            return response()->json($customers);
        } catch (\Exception $e) {
            return response()->json([], 200);
        }
    })->name('api.customers');

    // API route for fetching templates by request type
    Route::get('/api/templates/by-request-type', [\App\Http\Controllers\Admin\TemplateController::class, 'getByRequestType'])->name('api.templates.by-request-type');

    // Dashboard route
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Receipt header/footer routes for wkhtmltopdf
    Route::get('/receipt/{ticket}/header', function (\App\Models\Ticket $ticket) {
        return view('receipts.header', compact('ticket'));
    })->name('receipt.header');
    
    Route::get('/receipt/{ticket}/footer', function (\App\Models\Ticket $ticket) {
        return view('receipts.footer', compact('ticket'));
    })->name('receipt.footer');

    // Profile routes
    Route::view('profile', 'profile')->name('profile');

    // Ticket routes - Specific routes MUST come before resource routes
    Route::get('tickets/assigned', [TicketController::class, 'assigned'])->name('tickets.assigned');
    Route::get('tickets/department', [TicketController::class, 'departmentTickets'])->name('tickets.department');
    Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    Route::post('tickets/{ticket}/approve-cancel', [TicketController::class, 'approveCancel'])->name('tickets.approve-cancel');
    Route::post('tickets/{ticket}/cancel-by-pic', [TicketController::class, 'cancelByPic'])->name('tickets.cancel-by-pic');
    Route::post('tickets/{ticket}/duplicate', [TicketController::class, 'duplicate'])->name('tickets.duplicate');
    Route::resource('tickets', TicketController::class);
    Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');
    Route::post('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('tickets/{ticket}/receipt', [TicketController::class, 'receipt'])->name('tickets.receipt');
    Route::get('tickets/{ticket}/print-receipt', [TicketController::class, 'printReceipt'])->name('tickets.print-receipt');
    Route::post('tickets/{ticket}/complete', [TicketController::class, 'complete'])->name('tickets.complete');
    Route::post('tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::patch('tickets/{ticket}/update-delivery-pic', [TicketController::class, 'updateDeliveryPic'])->name('tickets.update-delivery-pic');
    Route::post('tickets/{ticket}/manager-approval', [TicketController::class, 'updateManagerApproval'])->name('tickets.manager-approval');
    Route::post('tickets/{ticket}/approve-manager', [TicketController::class, 'approveManager'])->name('tickets.approve-manager');
    Route::post('tickets/{ticket}/approve-gm', [TicketController::class, 'approveGm'])->name('tickets.approve-gm');

    // Courier routes
    Route::get('courier/packages', [\App\Http\Controllers\CourierController::class, 'index'])->name('courier.index');
    Route::get('courier/today', [\App\Http\Controllers\CourierController::class, 'today'])->name('courier.today');
    Route::get('courier/manifest/{courierName}', [\App\Http\Controllers\CourierController::class, 'printManifest'])->name('courier.manifest');
    Route::get('courier/label/{ticket}', [\App\Http\Controllers\CourierController::class, 'printLabel'])->name('courier.label');
    Route::get('courier/receipt/{ticket}', [\App\Http\Controllers\CourierController::class, 'printDeliveryReceipt'])->name('courier.receipt');

    // Reports routes
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/it', [ReportController::class, 'it'])->name('reports.it');
    Route::get('reports/it/export', [ReportController::class, 'itExport'])->name('reports.it.export');

    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

    // Super Admin routes - User Management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserManagementController::class)->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ]);

        // Priority Management (Super Admin only)
        Route::get('priorities', [\App\Http\Controllers\Admin\PriorityController::class, 'index'])->name('priorities.index');
        Route::get('priorities/{priority}/edit', [\App\Http\Controllers\Admin\PriorityController::class, 'edit'])->name('priorities.edit');
        Route::put('priorities/{priority}', [\App\Http\Controllers\Admin\PriorityController::class, 'update'])->name('priorities.update');

        // Template Management (Super Admin only)
        Route::resource('templates', \App\Http\Controllers\Admin\TemplateController::class);
        
        // Customer Management (Super Admin only)
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
    });

    // Super Admin routes - System Management
    Route::resource('departments', DepartmentController::class);
    Route::resource('request-types', RequestTypeController::class);

    // Request Type Fields Management
    Route::post('request-types/{requestType}/fields', [\App\Http\Controllers\Admin\RequestTypeFieldController::class, 'store'])->name('request-types.fields.store');
    Route::put('request-types/{requestType}/fields/{field}', [\App\Http\Controllers\Admin\RequestTypeFieldController::class, 'update'])->name('request-types.fields.update');
    Route::delete('request-types/{requestType}/fields/{field}', [\App\Http\Controllers\Admin\RequestTypeFieldController::class, 'destroy'])->name('request-types.fields.destroy');

    // Priority routes
    Route::get('priorities', [PriorityController::class, 'index'])->name('priorities.index');
});
require __DIR__ . '/auth.php';
