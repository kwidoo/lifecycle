<?php

namespace Kwidoo\Lifecycle\Contracts\Authorizers;

interface AuthorizerFactory
{
    public function resolve(string $context): Authorizer;
}
