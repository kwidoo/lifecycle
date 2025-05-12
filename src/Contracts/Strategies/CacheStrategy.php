<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

interface CacheStrategy
{
    /**
     * Execute with cache handling
     *
     * @param callable $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed;
}