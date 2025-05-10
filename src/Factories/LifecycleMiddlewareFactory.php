<?php

namespace Kwidoo\Lifecycle\Factories;

use Kwidoo\Lifecycle\Core\Pipeline\MiddlewarePipelineBuilder;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;

class LifecycleMiddlewareFactory
{
    public function __construct(
        protected MiddlewarePipelineBuilder $pipelineBuilder
    ) {}

    /**
     * Get middleware array for the given options
     *
     * @param LifecycleOptionsData $options
     * @return array
     */
    public function forOptions(LifecycleOptionsData $options): array
    {
        return $this->pipelineBuilder->build($options);
    }
}
