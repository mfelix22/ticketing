<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestComment extends Model
{
    protected $fillable = ['request_id', 'user_id', 'comment'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
