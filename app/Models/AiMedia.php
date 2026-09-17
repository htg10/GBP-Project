<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMedia extends Model
{
    protected $fillable = [
        'agency_id', 'client_id', 'created_by', 'type', 'prompt', 'image_data', 'model', 'source',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
