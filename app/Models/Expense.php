<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['agency_id', 'date', 'description', 'category', 'method', 'amount'];
    protected $casts = ['date' => 'date', 'amount' => 'decimal:2'];
}
