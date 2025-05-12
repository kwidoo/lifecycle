<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Closure;

interface Cacheable
{
    /**
     * Execute a callback with cache handling
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(Closure $callback): mixed;
}