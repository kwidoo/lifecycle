<?php

namespace Kwidoo\Lifecycle\Strategies\Authorization;

use Kwidoo\Lifecycle\Contracts\Factories\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Strategies\AuthorizationStrategy;
use Kwidoo\Lifecycle\Data\LifecycleContextData;

class DefaultAuthorizationStrategy implements AuthorizationStrategy
{
    /**
     * @param ErrorReportable $errorReporter
     */
    public function __construct(
        protected AuthorizerFactory $factory,
    ) {}

    /**
     * Authorize the action for the given context data
     *
     * @param LifecycleContextData $data
     * @return void
     */
    public function authorize(LifecycleContextData $data): void
    {
        $authorizer = $this->factory->resolve($data->resource);
        $authorizer->authorize($data->action, $data->context);
    }
}
