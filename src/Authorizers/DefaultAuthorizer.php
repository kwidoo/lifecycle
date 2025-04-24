<?php

namespace Kwidoo\Lifecycle\Authorizers;

use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Spatie\LaravelData\Contracts\BaseData;

class DefaultAuthorizer implements Authorizer
{
    /**
     * @param string $ability
     * @param BaseData|null $context
     *
     * @return void
     */
    public function authorize(string $ability, ?BaseData $context = null): void
    {
    }
}
