<?php

namespace Kwidoo\Lifecycle\Factories;

use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Middleware\ErrorCatcherMiddleware;
use Kwidoo\Lifecycle\Middleware\WithEventsMiddleware;
use Kwidoo\Lifecycle\Middleware\WithLoggingMiddleware;
use Kwidoo\Lifecycle\Middleware\WithTransactionsMiddleware;

class LifecycleMiddlewareFactory
{
    public function __construct(
        protected LifecycleStrategyResolver $resolver
    ) {}

    public function forOptions(LifecycleOptionsData $options): array
    {
        $strategies = $this->resolver->resolve($options);

        return [
            new ErrorCatcherMiddleware($strategies),
            new WithEventsMiddleware($strategies->eventable),
            new WithLoggingMiddleware($strategies->loggable),
            new WithTransactionsMiddleware($strategies->transactional),
        ];
    }
}
