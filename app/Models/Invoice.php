<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'agency_id', 'client_id', 'invoice_number', 'issue_date', 'due_date',
        'status', 'subtotal', 'gst_total', 'total', 'notes', 'paid_at',
    ];
    protected $casts = [
        'issue_date' => 'date', 'due_date' => 'date', 'paid_at' => 'datetime',
        'subtotal' => 'decimal:2', 'gst_total' => 'decimal:2', 'total' => 'decimal:2',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }

    public function isOverdue(): bool
    {
        return $this->status === 'SENT' && $this->due_date && $this->due_date->isPast();
    }
}
