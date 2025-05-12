<?php

namespace Kwidoo\Lifecycle\Core\Engine;

use Illuminate\Pipeline\Pipeline;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\CQRS\CQRSService;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
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
        protected CQRSService $cqrsService,
    ) {}

    /**
     * Run the lifecycle for the given data and callback
     *
     * @param LifecycleContextData $data Context data or legacy lifecycle data
     * @param callable $callback The callback to execute within the lifecycle
     * @param LifecycleOptionsData|null $options Optional settings for this lifecycle execution
     * @return mixed The result of the lifecycle execution
     */
    public function run(LifecycleContextData $data, callable $callback, $options = null): mixed
    {
        $options ??= new LifecycleOptionsData();

        // Handle authorization if enabled
        if ($options->authEnabled) {
            $this->authorize($data);
        }

        // Route to appropriate execution path based on options
        if ($options->useCQRS) {
            return $this->runWithCQRSCommand($data, $callback, $options);
        }

        if ($options->asQuery) {
            return $this->runWithCQRSQuery($data, $callback, $options);
        }

        // Standard flow with LifecycleContextData
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

        return $result;
    }

    /**
     * Run with CQRS Command mode - handle command processing via aggregates
     *
     * @param LifecycleContextData $data
     * @param callable $callback Function that creates and dispatches the command (optional in CQRS mode)
     * @param LifecycleOptionsData $options
     * @return mixed
     */
    protected function runWithCQRSCommand(
        LifecycleContextData $data,
        callable $callback,
        LifecycleOptionsData $options
    ): mixed {
        // Create standard lifecycle data structure for pipeline consistency
        $lifeCycleData = $data instanceof LifecycleData
            ? $data
            : $this->createLifecycleData($data, new LifecycleResultData());

        // First run through the middleware pipeline to handle all cross-cutting concerns
        return $this->pipeline
            ->send($lifeCycleData)
            ->through($this->middlewareFactory->forOptions($options))
            ->then(function ($lifeCycleData) use ($callback) {
                // The user has two options for CQRS command handling:

                // Option 1: Let the CQRSService create and dispatch a command automatically
                // based on the action.resource mapping in config
                try {
                    $result = $this->cqrsService->handleCommand($lifeCycleData);
                    $lifeCycleData->result = $result;
                    return $result;
                } catch (\InvalidArgumentException $e) {
                    // If no automatic mapping exists, fall back to Option 2
                }

                // Option 2: Use the provided callback to handle command creation and dispatching
                // This gives more flexibility but requires the user to write more code
                $result = $callback($lifeCycleData);
                $lifeCycleData->result = $result;
                return $result;
            });
    }

    /**
     * Run with CQRS Query mode - bypass most middleware and access read models
     *
     * @param LifecycleContextData $data
     * @param callable $callback Function that queries the read model (optional in CQRS mode)
     * @param LifecycleOptionsData $options
     * @return mixed
     */
    protected function runWithCQRSQuery(
        LifecycleContextData $data,
        callable $callback,
        LifecycleOptionsData $options
    ): mixed {
        // For query mode, we use a more lightweight pipeline
        // Create standard lifecycle data structure for pipeline consistency
        $lifeCycleData = $data instanceof LifecycleData
            ? $data
            : $this->createLifecycleData($data, new LifecycleResultData());

        // Only apply minimal middleware for queries - typically just auth and logging
        $queryOptions = (clone $options)
            ->withoutTrx()     // No transactions for queries
            ->withoutEvents(); // No events for queries

        // Execute the pipeline with minimal middleware
        return $this->pipeline
            ->send($lifeCycleData)
            ->through($this->middlewareFactory->forQueryOptions($queryOptions))
            ->then(function ($lifeCycleData) use ($callback) {
                // Similar to command mode, provide two options for query handling:

                // Option 1: Let the CQRSService handle the query automatically
                try {
                    $result = $this->cqrsService->query($lifeCycleData);
                    $lifeCycleData->result = $result;
                    return $result;
                } catch (\InvalidArgumentException $e) {
                    // If automatic handling fails, fall back to Option 2
                }

                // Option 2: Use the provided callback for customized query handling
                $result = $callback($lifeCycleData);
                $lifeCycleData->result = $result;
                return $result;
            });
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
     * @param LifecycleContextData $data
     * @return void
     */
    protected function authorize(LifecycleContextData $data): void
    {
        $resource = $data->resource;
        $action = $data->action;
        $context = $data->context;

        $authorizer = $this->authorizerFactory->resolve($resource);
        $authorizer->authorize($action, $context);
    }
}
