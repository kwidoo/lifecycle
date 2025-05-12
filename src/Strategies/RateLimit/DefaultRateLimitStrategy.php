<?php

namespace Kwidoo\Lifecycle\Strategies\RateLimit;

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\RateLimitable;
use Kwidoo\Lifecycle\Contracts\Strategies\RateLimitStrategy;

class DefaultRateLimitStrategy implements RateLimitStrategy
{
    /**
     * @param RateLimitable $rateLimitable
     */
    public function __construct(
        protected RateLimitable $rateLimitable
    ) {}

    /**
     * Execute with ratelimit handling
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed
    {
        return $this->rateLimitable->execute($callback);
    }
}
