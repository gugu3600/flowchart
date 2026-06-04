<?php

namespace App\Models;

use Database\Factories\FlowEdgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlowEdge extends Model
{
    /** @use HasFactory<FlowEdgeFactory> */
    use HasFactory;

    protected $fillable = [
        'flow_id',
        'source_node_id',
        'target_node_id',
        'label',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class);
    }

    public function sourceNode(): BelongsTo
    {
        return $this->belongsTo(FlowNode::class, 'source_node_id');
    }

    public function targetNode(): BelongsTo
    {
        return $this->belongsTo(FlowNode::class, 'target_node_id');
    }
}
