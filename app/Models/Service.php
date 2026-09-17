<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = ['agency_id', 'service_category_id', 'name', 'price', 'gst_percent', 'status'];
    protected $casts = ['price' => 'decimal:2', 'gst_percent' => 'decimal:2'];

    public function category(): BelongsTo { return $this->belongsTo(ServiceCategory::class, 'service_category_id'); }
}
