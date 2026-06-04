<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'config' => $this->config,
            'nodes' => FlowNodeResource::collection($this->whenLoaded('nodes')),
            'edges' => FlowEdgeResource::collection($this->whenLoaded('edges')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
