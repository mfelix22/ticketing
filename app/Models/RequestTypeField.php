<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestTypeField extends Model
{
    protected $fillable = [
        'request_type_id',
        'field_name',
        'field_key',
        'field_type',
        'is_required',
        'field_options',
        'placeholder',
        'validation_rules',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'field_options' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the request type that owns this field.
     */
    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }
}
