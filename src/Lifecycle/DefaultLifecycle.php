<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Illuminate\Pipeline\Pipeline;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
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
    ) {
    }

    /**
     * @param LifecycleData $data
     * @param callable $callback
     * @param mixed $options
     *
     * @return mixed
     */
    public function run(LifecycleData $data, callable $callback, $options): mixed
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
     * @param LifecycleData $data
     *
     * @return void
     */
    protected function authorize(LifecycleData $data): void
    {
        $authorizer = $this->authorizerFactory->resolve($data->resource);
        $authorizer->authorize($data->action, $data->context);
    }
}
