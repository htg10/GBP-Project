<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = ['agency_id', 'client_id', 'provider', 'access_token', 'refresh_token', 'expires_at', 'meta'];
    protected $casts = ['meta' => 'array', 'expires_at' => 'datetime'];
    protected $hidden = ['access_token', 'refresh_token'];
}
