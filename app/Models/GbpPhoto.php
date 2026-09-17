<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GbpPhoto extends Model
{
    protected $fillable = [
        'gbp_location_id', 'image', 'caption', 'status', 'scheduled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GbpLocation::class, 'gbp_location_id');
    }
}
