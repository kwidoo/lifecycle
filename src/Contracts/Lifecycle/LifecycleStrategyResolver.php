<?php

namespace Kwidoo\Lifecycle\Contracts\Lifecycle;

use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;

interface LifecycleStrategyResolver
{
    /**
     * @param LifecycleOptionsData $options
     *
     * @return LifecycleStrategies
     */
    public function resolve(LifecycleOptionsData $options): LifecycleStrategies;
}
