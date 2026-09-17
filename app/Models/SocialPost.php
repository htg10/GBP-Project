<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPost extends Model
{
    protected $fillable = ['agency_id', 'client_id', 'platform', 'body', 'media_urls', 'status', 'scheduled_at', 'published_at'];
    protected $casts = ['media_urls' => 'array', 'scheduled_at' => 'datetime', 'published_at' => 'datetime'];
}
