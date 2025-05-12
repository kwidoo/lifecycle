<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Closure;

interface RateLimitable
{
    /**
     * Execute a callback with ratelimit handling
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed;
}
