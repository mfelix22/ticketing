<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'request_type_id',
        'department_id',
        'title_template',
        'default_priority_id',
        'field_defaults',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'field_defaults' => 'array',
        'is_active' => 'boolean',
    ];

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function defaultPriority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'default_priority_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForRequestType($query, $requestTypeId)
    {
        return $query->where('request_type_id', $requestTypeId);
    }
}
