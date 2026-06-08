<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogicDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'inputs' => $this->inputs,
            'output' => $this->output,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
