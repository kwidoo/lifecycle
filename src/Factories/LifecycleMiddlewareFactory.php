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

    /**
     * Get middleware array specific for query operations
     * Typically lighter-weight than command operations
     *
     * @param LifecycleOptionsData $options
     * @return array
     */
    public function forQueryOptions(LifecycleOptionsData $options): array
    {
        // For now, we use the standard pipeline builder
        // In the future, this could be a specialized builder for queries
        return $this->pipelineBuilder->buildForQueries($options);
    }
}
