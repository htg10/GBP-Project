<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agency extends Model
{
    protected $fillable = ['name', 'slug', 'white_label'];
    protected $casts = ['white_label' => 'array'];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function clients(): HasMany { return $this->hasMany(Client::class); }
    public function subscription(): HasOne { return $this->hasOne(Subscription::class); }
    public function reviews(): HasMany { return $this->hasMany(Review::class); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
}
