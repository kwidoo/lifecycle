<?php

namespace Kwidoo\Lifecycle\Strategies\Cache;

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\Cacheable;
use Kwidoo\Lifecycle\Contracts\Strategies\CacheStrategy;

class DefaultCacheStrategy implements CacheStrategy
{
    /**
     * @param Cacheable $cacheable
     */
    public function __construct(
        protected Cacheable $cacheable
    ) {}

    /**
     * Execute with cache handling
     *
     * @param Closure $callback
     * @return mixed
     */
    public function execute(callable $callback): mixed
    {
        return $this->cacheable->execute($callback);
    }
}