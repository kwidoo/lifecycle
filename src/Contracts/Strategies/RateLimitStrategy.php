<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

interface RateLimitStrategy
{
    /**
     * Execute with ratelimit handling
     *
     * @param callable $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed;
}
