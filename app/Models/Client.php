<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['agency_id', 'name', 'industry', 'phone', 'email', 'gstin', 'billing_address'];

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function locations(): HasMany { return $this->hasMany(GbpLocation::class); }
    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
}
