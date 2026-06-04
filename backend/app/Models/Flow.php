<?php

namespace App\Models;

use Database\Factories\FlowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flow extends Model
{
    /** @use HasFactory<FlowFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(FlowNode::class);
    }

    public function edges(): HasMany
    {
        return $this->hasMany(FlowEdge::class);
    }
}
