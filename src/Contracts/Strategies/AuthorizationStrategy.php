<?php

namespace Kwidoo\Lifecycle\Contracts\Strategies;

use Kwidoo\Lifecycle\Data\LifecycleContextData;

interface AuthorizationStrategy
{
    /**
     * Authorize the action for the given context data
     *
     * @param LifecycleContextData $data
     * @return void
     * @throws AuthorizationException
     */
    public function authorize(LifecycleContextData $data): void;
}
