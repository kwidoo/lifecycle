<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Illuminate\Pipeline\Pipeline;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
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
     * @param LifecycleContextData|LifecycleData $data
     * @param callable $callback
     * @param LifecycleOptionsData|null $options
     *
     * @return mixed
     */
    public function run(LifecycleContextData|LifecycleData $data, callable $callback, $options = null): mixed
    {
        $options ??= new LifecycleOptionsData();
        if ($options->authEnabled) {
            $this->authorize($data);
        }

        return $this->pipeline
            ->send($data)
            ->through($this->middlewareFactory->forOptions($options))
            ->then(fn($data) => $callback($data));
    }

    /**
     * @param LifecycleContextData|LifecycleData $data
     *
     * @return void
     */
    protected function authorize(LifecycleContextData|LifecycleData $data): void
    {
        $authorizer = $this->authorizerFactory->resolve($data->resource);
        $authorizer->authorize($data->action, $data->context);
    }
}
