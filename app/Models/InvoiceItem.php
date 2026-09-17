<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'service_id', 'description', 'quantity', 'unit_price', 'gst_percent', 'amount'];
    protected $casts = [
        'quantity' => 'decimal:2', 'unit_price' => 'decimal:2',
        'gst_percent' => 'decimal:2', 'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
