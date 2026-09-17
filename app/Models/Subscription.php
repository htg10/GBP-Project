<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['agency_id', 'plan', 'credit_balance', 'monthly_credits', 'credits_reset_at', 'status', 'provider', 'external_id', 'renews_at'];
    protected $casts = ['renews_at' => 'datetime', 'credits_reset_at' => 'datetime'];

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
}
