<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sla_response_hours',
        'sla_resolution_hours',
        'color',
        'icon'
    ];

    /**
     * Calculate due date from current time based on SLA resolution hours
     */
    public function calculateDueDate($startTime = null)
    {
        $start = $startTime ?? now();
        return $start->addHours($this->sla_resolution_hours);
    }

    /**
     * Get badge class for display
     */
    public function getBadgeClassAttribute()
    {
        return "badge-{$this->color}";
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
