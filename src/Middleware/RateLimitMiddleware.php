<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\RateLimitStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class RateLimitMiddleware
{
    /**
     * @param RateLimitStrategy $rateLimitStrategy
     */
    public function __construct(
        protected RateLimitStrategy $rateLimitStrategy
    ) {}

    /**
     * Handle the lifecycle request with rateLimit handling
     *
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        return $this->rateLimitStrategy
            ->execute(
                fn() => $next($data)
            );
    }
}
