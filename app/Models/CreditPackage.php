<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    protected $fillable = ['name', 'credits', 'price', 'gst_rate', 'is_active', 'sort'];
    protected $casts = ['is_active' => 'boolean'];

    public function baseAmount(): float
    {
        return round($this->price / (1 + $this->gst_rate / 100), 2);
    }

    public function gstAmount(): float
    {
        return round($this->price - $this->baseAmount(), 2);
    }
}
