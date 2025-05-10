<?php

namespace Kwidoo\Lifecycle\Core\Engine;

use Illuminate\Pipeline\Pipeline;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;

class DefaultLifecycle implements Lifecycle
{
    public function __construct(
        protected AuthorizerFactory $authorizerFactory,
        protected LifecycleStrategyResolver $resolver,
        protected Pipeline $pipeline,
        protected LifecycleMiddlewareFactory $middlewareFactory,
    ) {}

    /**
     * Run the lifecycle for the given data and callback
     *
     * @param LifecycleContextData|LifecycleData $data Context data or legacy lifecycle data
     * @param callable $callback The callback to execute within the lifecycle
     * @param LifecycleOptionsData|null $options Optional settings for this lifecycle execution
     * @return mixed The result of the lifecycle execution
     */
    public function run(LifecycleContextData|LifecycleData $data, callable $callback, $options = null): mixed
    {
        $options ??= new LifecycleOptionsData();

        // Handle authorization if enabled
        if ($options->authEnabled) {
            $this->authorize($data);
        }

        // If using legacy LifecycleData, wrap it for backward compatibility
        if ($data instanceof LifecycleData) {
            $result = $this->runWithLegacyData($data, $callback, $options);
        } else {
            // Create a new result data object and combine with context data
            $resultData = new LifecycleResultData();
            $lifeCycleData = $this->createLifecycleData($data, $resultData);

            // Execute the pipeline with combined data
            $result = $this->pipeline
                ->send($lifeCycleData)
                ->through($this->middlewareFactory->forOptions($options))
                ->then(function ($lifeCycleData) use ($callback) {
                    $result = $callback($lifeCycleData);
                    $lifeCycleData->result = $result;
                    return $result;
                });
        }

        return $result;
    }

    /**
     * Run with legacy LifecycleData for backward compatibility
     *
     * @param LifecycleData $data
     * @param callable $callback
     * @param LifecycleOptionsData $options
     * @return mixed
     */
    protected function runWithLegacyData(LifecycleData $data, callable $callback, LifecycleOptionsData $options): mixed
    {
        return $this->pipeline
            ->send($data)
            ->through($this->middlewareFactory->forOptions($options))
            ->then(fn($data) => $callback($data));
    }

    /**
     * Create a combined LifecycleData object from context and result
     *
     * @param LifecycleContextData $contextData
     * @param LifecycleResultData $resultData
     * @return LifecycleData
     */
    protected function createLifecycleData(LifecycleContextData $contextData, LifecycleResultData $resultData): LifecycleData
    {
        return new LifecycleData(
            action: $contextData->action,
            resource: $contextData->resource,
            context: $contextData->context,
            result: $resultData->result,
        );
    }

    /**
     * Authorize the lifecycle action using the appropriate authorizer
     *
     * @param LifecycleContextData|LifecycleData $data
     * @return void
     */
    protected function authorize(LifecycleContextData|LifecycleData $data): void
    {
        $resource = $data->resource;
        $action = $data->action;
        $context = $data->context;

        $authorizer = $this->authorizerFactory->resolve($resource);
        $authorizer->authorize($action, $context);
    }
}
