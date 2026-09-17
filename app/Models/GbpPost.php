<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GbpPost extends Model
{
    protected $fillable = [
        'gbp_location_id', 'type', 'body', 'cta_url', 'status', 'scheduled_at', 'published_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GbpLocation::class, 'gbp_location_id');
    }
}
