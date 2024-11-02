<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Generation extends Model
{
    protected $fillable = [
        'user_id',
        'prompt',
        'model',
        'version',
        'parameters',
        'prediction_id',
        'status',
        'image_url',
        'local_image_path',
        'result'
    ];

    protected $casts = [
        'parameters' => 'array',
        'result' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
