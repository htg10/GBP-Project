<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ['agency_id', 'client_id', 'name', 'phone', 'email', 'source', 'stage', 'score', 'notes'];
}
