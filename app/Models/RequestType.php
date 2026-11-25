<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestType extends Model
{
    protected $fillable = ['name', 'description', 'requires_external_handling'];

    protected $casts = [
        'requires_external_handling' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(RequestTypeField::class)->orderBy('order');
    }
}
