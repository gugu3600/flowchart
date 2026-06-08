<?php

namespace App\Http\Controllers\api;

use App\Http\Requests\LogicDefinition\StoreLogicDefinitionRequest;
use App\Http\Requests\LogicDefinition\UpdateLogicDefinitionRequest;
use App\Http\Resources\LogicDefinitionResource;
use App\Models\LogicDefinition;
use App\Services\LogicDefinition\LogicDefinitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LogicDefinitionController extends BaseController
{
    public function __construct(
        private readonly LogicDefinitionService $service,
    ) {}

    public function index(): JsonResponse
    {
        $definitions = $this->service->allForUser(Auth::id());

        return $this->success(
            ['logics' => LogicDefinitionResource::collection($definitions)],
            'Logic definitions retrieved',
        );
    }

    public function store(StoreLogicDefinitionRequest $request): JsonResponse
    {
        $definition = $this->service->create(Auth::id(), $request->validated());

        return $this->success(
            ['logic' => new LogicDefinitionResource($definition)],
            'Logic definition created',
            201,
        );
    }

    public function show(LogicDefinition $logicDefinition): JsonResponse
    {
        $definition = $this->service->findForUser($logicDefinition->id, Auth::id());

        return $this->success(
            ['logic' => new LogicDefinitionResource($definition)],
            'Logic definition retrieved',
        );
    }

    public function update(UpdateLogicDefinitionRequest $request, LogicDefinition $logicDefinition): JsonResponse
    {
        $definition = $this->service->update($logicDefinition->id, Auth::id(), $request->validated());

        return $this->success(
            ['logic' => new LogicDefinitionResource($definition)],
            'Logic definition updated',
        );
    }

    public function destroy(LogicDefinition $logicDefinition): JsonResponse
    {
        $this->service->delete($logicDefinition->id, Auth::id());

        return $this->success([], 'Logic definition deleted');
    }
}
