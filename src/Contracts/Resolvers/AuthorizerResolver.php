<?php

namespace Kwidoo\Lifecycle\Contracts\Resolvers;

use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;

/**
 * Interface for resolving authorizer implementations
 */
interface AuthorizerResolver
{
    /**
     * Resolve an appropriate authorizer implementation based on resource and action
     *
     * @param string $resource The resource being accessed
     * @param string $action The action being performed
     * @return Authorizer The resolved authorizer implementation
     */
    public function resolveForResourceAndAction(string $resource, string $action): Authorizer;
}
