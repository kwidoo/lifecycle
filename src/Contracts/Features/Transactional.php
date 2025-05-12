<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Closure;

interface Transactional
{
    /**
     * Execute a callback within a database transaction
     *
     * @param Closure $callback
     * @return LifecycleResultData
     */
    public function execute(Closure $callback): mixed;
}
