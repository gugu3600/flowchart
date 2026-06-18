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
        $flowId = request()->query('flow_id');
        $definitions = $flowId
            ? $this->service->allForUserAndFlow(Auth::id(), (int) $flowId)
            : $this->service->allForUser(Auth::id());
        $count = $this->service->logicCount(Auth::id());

        return $this->success([
            'logics' => LogicDefinitionResource::collection($definitions),
            'logic_count' => $count,
            'max_slots' => $this->service->maxLogicSlots(Auth::id()),
        ], 'Logic definitions retrieved');
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

    public function show(LogicDefinition $logic): JsonResponse
    {
        $definition = $this->service->findForUser($logic->id, Auth::id());

        return $this->success(
            ['logic' => new LogicDefinitionResource($definition)],
            'Logic definition retrieved',
        );
    }

    public function update(UpdateLogicDefinitionRequest $request, LogicDefinition $logic): JsonResponse
    {
        $definition = $this->service->update($logic->id, Auth::id(), $request->validated());

        return $this->success(
            ['logic' => new LogicDefinitionResource($definition)],
            'Logic definition updated',
        );
    }

    public function destroy(LogicDefinition $logic): JsonResponse
    {
        $this->service->delete($logic->id, Auth::id());

        return $this->success([], 'Logic definition deleted');
    }
}
