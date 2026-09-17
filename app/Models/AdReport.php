<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdReport extends Model
{
    protected $fillable = ['agency_id', 'client_id', 'network', 'campaign', 'spend', 'clicks', 'impressions', 'conversions', 'leads', 'period_start', 'period_end'];
    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'spend' => 'decimal:2'];
}
