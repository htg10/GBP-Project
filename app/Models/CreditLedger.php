<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedger extends Model
{
    protected $table = 'credit_ledger';

    protected $fillable = [
        'agency_id', 'user_id', 'amount', 'balance_after', 'action', 'description',
    ];

    public function agency(): BelongsTo { return $this->belongsTo(Agency::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
