<?php

namespace Kwidoo\Lifecycle\Contracts\Lifecycle;

use Kwidoo\Lifecycle\Data\LifecycleData;

interface Lifecycle
{
    /**
     * @param LifecycleData $data
     * @param callable $callback
     * @param mixed $options
     *
     * @return mixed
     */
    public function run(LifecycleData $data, callable $callback, $options): mixed;
}
