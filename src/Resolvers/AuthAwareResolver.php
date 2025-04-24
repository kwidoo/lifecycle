<?php

namespace Kwidoo\Mere\Resolvers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;

class AuthAwareResolver
{
    public function __construct(
        protected AuthFactory $auth,
        protected Container $container
    ) {
    }

    public function resolve($unauthenticated, $authenticated = null, ?string $guard = null)
    {
        $guardInstance = $guard
            ? $this->auth->guard($guard)
            : $this->auth->guard();

        return $guardInstance->check() && ($authenticated
            ? (is_string($unauthenticated) ? $this->container->make($authenticated) : $authenticated)
            : (is_string($unauthenticated) ? $this->container->make($unauthenticated) : $unauthenticated));
    }
}
