<?php

namespace Kwidoo\Lifecycle\Contracts\Lifecycle;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;

interface Lifecycle
{
    /**
     * Run the lifecycle for the given data and callback
     *
     * @param LifecycleContextData $data Context data or legacy lifecycle data
     * @param callable $callback The callback to execute within the lifecycle
     * @param LifecycleOptionsData|null $options Optional settings for this lifecycle execution
     * @return mixed The result of the lifecycle execution
     */
    public function run(LifecycleContextData $data, callable $callback, $options = null): mixed;
}
