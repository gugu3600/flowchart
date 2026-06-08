<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogicDefinition extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'inputs',
        'output',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
