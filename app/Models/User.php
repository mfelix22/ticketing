<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method bool isSuperAdmin()
 * @method bool isAdmin()
 * @method bool canManageUsers()
 * @method bool canManageDepartments()
 * @method bool canManageRequestTypes()
 * @method bool canManagePriorities()
 * @method bool canManageCustomers()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'company',
        'department_id',
        'intra_mail_address',
        'extra_mail_address',
        'status',
        'is_pic',
        'role'
    ];

    public function usernameOrEmail()
    {
        return filter_var($this->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_pic' => 'boolean',
    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    /**
     * Get the username used for authentication.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return \App\Models\User|null
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function handledTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'handling_user_id');
    }

    public function acceptedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'acceptance_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class, 'uploaded_by');
    }

    /**
     * Check if user can manage tickets (based on department, is_pic, or status)
     * Users can manage tickets if:
     * - They have is_pic=true AND belong to the same department as the ticket, OR
     * - They have Approver/Both status (can manage any ticket), OR
     * - They are assigned to or handling the ticket
     */
    public function canManageTicket(Ticket $ticket): bool
    {
        // Approvers and Both can manage any ticket
        if (in_array($this->status, ['Approver', 'Both'])) {
            return true;
        }

        // PICs can manage tickets in their own department
        if ($this->is_pic && $this->department_id && $this->department_id === $ticket->department_id) {
            return true;
        }

        // Users assigned to or handling the ticket can manage it
        if ($this->id === $ticket->assigned_to || $this->id === $ticket->handling_user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can change ticket status
     */
    public function canChangeTicketStatus(Ticket $ticket): bool
    {
        return $this->canManageTicket($ticket);
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is a manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is an operations manager
     */
    public function isOperationsManager(): bool
    {
        return $this->role === 'operations_manager';
    }

    /**
     * Check if user is an admin or super admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage departments
     */
    public function canManageDepartments(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage request types
     */
    public function canManageRequestTypes(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage priorities
     */
    public function canManagePriorities(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage customers
     * Accessible by Super Admin, Operations Manager, or users in the Billing Collection department.
     */
    public function canManageCustomers(): bool
    {
        if ($this->isSuperAdmin() || $this->isOperationsManager()) {
            return true;
        }

        return (bool) ($this->department && $this->department->name === 'Billing Collection');
    }
}
