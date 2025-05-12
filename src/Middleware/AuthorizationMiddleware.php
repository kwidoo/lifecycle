<?php

namespace Kwidoo\Lifecycle\Middleware;

use Kwidoo\Lifecycle\Contracts\Factories\AuthorizerFactory;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Closure;

class AuthorizationMiddleware
{
    /**
     * @param AuthorizerFactory $authorizerFactory
     */
    public function __construct(
        protected AuthorizerFactory $authorizerFactory
    ) {}

    /**
     * Handle the lifecycle request with authorization
     *
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        $authorizer = $this->authorizerFactory->resolve($data->resource);
        $authorizer->authorize($data->action, $data->context);

        return $next($data);
    }
}
