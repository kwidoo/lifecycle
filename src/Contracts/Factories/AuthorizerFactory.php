<?php

namespace Kwidoo\Lifecycle\Contracts\Factories;

use Kwidoo\Lifecycle\Contracts\Features\Authorizer;

interface AuthorizerFactory
{
    public function resolve(string $context): Authorizer;
}
