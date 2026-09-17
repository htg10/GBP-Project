<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GbpLocation extends Model
{
    protected $fillable = ['client_id', 'google_name', 'title', 'address'];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function posts(): HasMany { return $this->hasMany(GbpPost::class); }
    public function photos(): HasMany { return $this->hasMany(GbpPhoto::class); }
}
