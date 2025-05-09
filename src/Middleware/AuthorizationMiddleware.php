<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;

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
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData|LifecycleData $data, Closure $next): mixed
    {
        $authorizer = $this->authorizerFactory->resolve($data->resource);
        $authorizer->authorize($data->action, $data->context);

        return $next($data);
    }
}
