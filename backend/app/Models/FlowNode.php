<?php

namespace App\Models;

use Database\Factories\FlowNodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlowNode extends Model
{
    /** @use HasFactory<FlowNodeFactory> */
    use HasFactory;

    protected $fillable = [
        'flow_id',
        'type',
        'label',
        'position_x',
        'position_y',
        'data',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'float',
            'position_y' => 'float',
            'data' => 'array',
            'config' => 'array',
        ];
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class);
    }

    public function sourceEdges(): HasMany
    {
        return $this->hasMany(FlowEdge::class, 'source_node_id');
    }

    public function targetEdges(): HasMany
    {
        return $this->hasMany(FlowEdge::class, 'target_node_id');
    }
}
