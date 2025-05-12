<?php

namespace Kwidoo\Lifecycle\Middleware;

use Closure;
use Kwidoo\Lifecycle\Contracts\Strategies\CacheStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class CacheMiddleware
{
    /**
     * @param CacheStrategy $cacheStrategy
     */
    public function __construct(
        protected CacheStrategy $cacheStrategy
    ) {}

    /**
     * Handle the lifecycle request with cache handling
     *
     * @param LifecycleContextData $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(LifecycleContextData $data, Closure $next): mixed
    {
        return $this->cacheStrategy
            ->execute(
                fn() => $next($data)
            );
    }
}
