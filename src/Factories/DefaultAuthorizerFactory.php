<?php

namespace Kwidoo\Lifecycle\Factories;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Authorizers\DefaultAuthorizer;


class DefaultAuthorizerFactory implements AuthorizerFactory
{
    public function __construct(
        protected Container $container
    ) {}

    /**
     * @param string $context
     *
     * @return Authorizer
     */
    public function resolve(string $context): Authorizer
    {
        return match ($context) {
            'quiz' => $this->container->make(DefaultAuthorizer::class),
            default => $this->container->make(DefaultAuthorizer::class),
        };
    }
}
