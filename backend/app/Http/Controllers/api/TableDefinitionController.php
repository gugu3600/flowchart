<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\TableDefinition\StoreTableDefinitionRequest;
use App\Http\Requests\TableDefinition\UpdateTableDefinitionRequest;
use App\Http\Resources\TableDefinitionResource;
use App\Models\TableDefinition;
use App\Services\TableDefinition\TableDefinitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TableDefinitionController extends BaseController
{
    public function __construct(
        private readonly TableDefinitionService $service,
    ) {}

    public function index(): JsonResponse
    {
        $definitions = $this->service->allForUser(Auth::id());

        return $this->success(
            ['tables' => TableDefinitionResource::collection($definitions)],
            'Table definitions retrieved',
        );
    }

    public function store(StoreTableDefinitionRequest $request): JsonResponse
    {
        $definition = $this->service->create(Auth::id(), $request->validated());

        return $this->success(
            ['table' => new TableDefinitionResource($definition)],
            'Table definition created',
            201,
        );
    }

    public function show(TableDefinition $table): JsonResponse
    {
        $definition = $this->service->findForUser($table->id, Auth::id());

        return $this->success(
            ['table' => new TableDefinitionResource($definition)],
            'Table definition retrieved',
        );
    }

    public function update(UpdateTableDefinitionRequest $request, TableDefinition $table): JsonResponse
    {
        $definition = $this->service->update($table->id, Auth::id(), $request->validated());

        return $this->success(
            ['table' => new TableDefinitionResource($definition)],
            'Table definition updated',
        );
    }

    public function destroy(TableDefinition $table): JsonResponse
    {
        $this->service->delete($table->id, Auth::id());

        return $this->success([], 'Table definition deleted');
    }
}
