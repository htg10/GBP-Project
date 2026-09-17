<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $fillable = [
        'agency_id', 'company_name', 'business_type', 'phone', 'email', 'gstin', 'address', 'state', 'logo',
        'bank_name', 'bank_account', 'ifsc', 'invoice_prefix', 'next_invoice_number',
        'default_gst', 'currency', 'round_total', 'tally_company_name', 'customer_ledger_group',
    ];

    protected $casts = ['round_total' => 'boolean', 'default_gst' => 'decimal:2'];
}
