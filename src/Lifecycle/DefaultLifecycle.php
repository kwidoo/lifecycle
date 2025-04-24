<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;

class DefaultLifecycle implements Lifecycle
{
    public function __construct(
        protected AuthorizerFactory $authorizerFactory,
        protected LifecycleStrategyResolver $resolver,
    ) {
    }

    /**
     * @param LifecycleData $data
     * @param callable $callback
     *
     * @return mixed
     */
    public function run(LifecycleData $data, callable $callback, $options): mixed
    {
        $options ??= new LifecycleOptionsData();
        if ($options->authEnabled) {
            $this->authorize($data);
        }

        $strategies = $this->resolver->resolve($options);
        try {
            return $strategies->eventable->executeEvents(
                $data,
                fn() => $strategies->loggable->executeLogging(
                    $data,
                    fn() => $strategies->transactional->executeTransactions(
                        fn() => $callback($data)
                    )
                )
            );
        } catch (\Throwable $e) {
            $strategies->eventable->dispatchError($data);
            $strategies->loggable->dispatchError($data);
            throw $e;
        }
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
