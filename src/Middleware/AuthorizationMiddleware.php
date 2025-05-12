<?php

namespace Kwidoo\Lifecycle\Middleware;

use Kwidoo\Lifecycle\Contracts\Factories\AuthorizerFactory;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\AuthorizationStrategy;

class AuthorizationMiddleware
{
    /**
     * @param AuthorizerFactory $authorizerFactory
     */
    public function __construct(
        protected AuthorizationStrategy $authorizationStrategy
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
        $this->authorizationStrategy->authorize($data);

        return $next($data);
    }
}
