<?php

namespace App\Providers;

use App\Repositories\flow\FlowRepository;
use App\Repositories\flow\FlowRepositoryInterface;
use App\Repositories\flow_edge\FlowEdgeRepository;
use App\Repositories\flow_edge\FlowEdgeRepositoryInterface;
use App\Repositories\flow_node\FlowNodeRepository;
use App\Repositories\flow_node\FlowNodeRepositoryInterface;
use App\Repositories\user\UserRepository;
use App\Repositories\user\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(FlowRepositoryInterface::class, FlowRepository::class);
        $this->app->bind(FlowNodeRepositoryInterface::class, FlowNodeRepository::class);
        $this->app->bind(FlowEdgeRepositoryInterface::class, FlowEdgeRepository::class);
    }
}
