<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Ticket extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'requester_id',
        'department_id',
        'request_type_id',
        'priority_id',
        'status_id',
        'title',
        'detail_request',
        'assigned_to',
        'due_date',
        'schedule_date',
        'receipt_date',
        'receipt_comment',
        'receipt_user_id',
        'is_continued',
        'finished_date',
        'note',
        'additional_note',
        'expenses_value',
        'other_costs',
        'usage_hours',
        'requires_manager_approval',
        'manager_approval_user_id',
        'manager_approved',
        'manager_approved_by',
        'manager_approved_at',
        'requires_gm_approval',
        'gm_approval_user_id',
        'gm_approved',
        'gm_approved_by',
        'gm_approved_at',
        'payment_flag',
        'handling_user',
        'handling_user_id',
        'acceptance_date',
        'acceptance_user_id',
        'request_number',
        'is_overdue_once'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'schedule_date' => 'datetime',
        'receipt_date' => 'datetime',
        'finished_date' => 'datetime',
        'acceptance_date' => 'datetime',
        'is_continued' => 'boolean',
        'is_overdue_once' => 'boolean',
        'requires_manager_approval' => 'boolean',
        'manager_approved' => 'boolean',
        'manager_approved_at' => 'datetime',
        'requires_gm_approval' => 'boolean',
        'gm_approved' => 'boolean',
        'gm_approved_at' => 'datetime',
        'detail_request' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->request_number)) {
                $deliveryType = null;
                if (is_array($ticket->detail_request) && isset($ticket->detail_request['delivery_type'])) {
                    $deliveryType = $ticket->detail_request['delivery_type'];
                }
                $ticket->request_number = self::generateRequestNumber($ticket->request_type_id, $deliveryType);
            }
        });

        // Mark the ticket if it ever became overdue while in active status (not Completed/Closed/Cancelled/Pending Approval)
        static::saving(function ($ticket) {
            if ($ticket->is_overdue_once) {
                return; // Once true, keep it sticky
            }

            $dueDate = $ticket->due_date ? Carbon::parse($ticket->due_date) : null;
            
            // Check if currently overdue and in active status
            if ($dueDate && $dueDate->lessThan(Carbon::now())) {
                // Check if status is NOT in finished/pending list
                $status = $ticket->status;
                if ($status && !in_array($status->name, ['Completed', 'Closed', 'Cancelled', 'Pending Approval'])) {
                    $ticket->is_overdue_once = true;
                }
            }
        });
    }

    public static function generateRequestNumber($requestTypeId = null, $deliveryType = null)
    {
        $year = date('Y');
        $prefix = "REQ-{$year}-";

        // Check if this is a Delivery request type
        if ($requestTypeId) {
            $requestType = \App\Models\RequestType::find($requestTypeId);
            if ($requestType && in_array($requestType->name, ['Delivery', 'Courier'])) {
                // Add C for Courier or E for Expedition
                if ($deliveryType === 'Courier') {
                    $prefix = "REQ-{$year}-C-";
                } elseif ($deliveryType === 'Expedition') {
                    $prefix = "REQ-{$year}-E-";
                }
            }
        }

        // Use the actual highest numeric suffix for this prefix.
        // LIKE with 4 underscores ensures exact length match so other
        // prefixes (e.g. REQ-2026-C-0001) are not accidentally picked up.
        $maxNumber = DB::table('requests')
            ->where('request_number', 'LIKE', "{$prefix}____")
            ->max(DB::raw("CAST(SUBSTRING(request_number, -4) AS UNSIGNED)"));

        $newNumber = ($maxNumber ? (int) $maxNumber : 0) + 1;

        // Format: REQ-2025-0001 or REQ-2025-C-0001 or REQ-2025-E-0001
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function acceptanceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acceptance_user_id');
    }

    public function receiptUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receipt_user_id');
    }

    public function handlingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handling_user_id');
    }

    public function managerApprovalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approval_user_id');
    }

    public function managerApprovedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function gmApprovalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_approval_user_id');
    }

    public function gmApprovedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_approved_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class, 'request_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class, 'request_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id');
    }

    public function getDurationSecondsAttribute()
    {
        if ($this->created_at && $this->finished_date) {
            $diff = $this->finished_date->timestamp - $this->created_at->timestamp;
            return max(0, $diff);
        }
        return null;
    }
}
