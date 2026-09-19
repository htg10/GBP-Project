<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'agency_id', 'gbp_location_id', 'google_review_id', 'reviewer_name', 'reviewer_photo',
        'star_rating', 'comment', 'sentiment', 'reply_text', 'draft_reply', 'replied_at', 'replied_by', 'review_time',
    ];
    protected $casts = ['replied_at' => 'datetime', 'review_time' => 'datetime'];

    public function location(): BelongsTo { return $this->belongsTo(GbpLocation::class, 'gbp_location_id'); }
}
