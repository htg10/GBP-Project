<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $fillable = ['agency_id', 'to_phone', 'from_phone', 'direction', 'template', 'body', 'status'];
}
