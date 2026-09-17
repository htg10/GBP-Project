<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['agency_id', 'plan', 'amount', 'currency', 'razorpay_order_id', 'razorpay_payment_id', 'status'];
}
