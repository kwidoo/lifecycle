<?php

namespace Kwidoo\Lifecycle\Resolvers;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Contracts\Resolvers\AuthorizerResolver;

/**
 * Resolves appropriate authorizer implementations based on resource, action, and authentication status
 */
class AuthAwareResolver implements AuthorizerResolver
{
    /**
     * @param AuthFactory $auth The authentication factory for checking user state
     * @param Container $container The container for resolving authorizer implementations
     */
    public function __construct(
        protected AuthFactory $auth,
        protected Container $container
    ) {}

    /**
     * Resolve an appropriate authorizer implementation based on resource and action
     *
     * @param string $resource The resource being accessed
     * @param string $action The action being performed
     * @return Authorizer The resolved authorizer implementation
     */
    public function resolveForResourceAndAction(string $resource, string $action): Authorizer
    {
        // Look up the authorizer configuration for this resource/action pair
        $config = config("lifecycle.authorizers.{$resource}.{$action}");

        if (!$config) {
            // Fall back to resource-level default authorizer
            $config = config("lifecycle.authorizers.{$resource}.default");
        }

        if (!$config) {
            // Fall back to global default authorizer
            $config = config("lifecycle.authorizers.default");
        }

        // Check if there are authenticated/unauthenticated variants
        if (is_array($config) && isset($config['authenticated']) && isset($config['unauthenticated'])) {
            return $this->resolveByAuthStatus(
                $config['unauthenticated'],
                $config['authenticated'],
                $config['guard'] ?? null
            );
        }

        // Otherwise use the config value directly
        return $this->resolveAuthorizer($config);
    }

    /**
     * Resolve authorizer based on authentication status
     *
     * @param string|callable $unauthenticated Authorizer for unauthenticated users
     * @param string|callable|null $authenticated Authorizer for authenticated users
     * @param string|null $guard Authentication guard to check
     * @return Authorizer The appropriate authorizer based on authentication status
     */
    protected function resolveByAuthStatus($unauthenticated, $authenticated = null, ?string $guard = null): Authorizer
    {
        $guardInstance = $guard
            ? $this->auth->guard($guard)
            : $this->auth->guard();

        $implementation = $guardInstance->check() && $authenticated
            ? $authenticated
            : $unauthenticated;

        return $this->resolveAuthorizer($implementation);
    }

    /**
     * Resolve an authorizer from a class name, callable, or instance
     *
     * @param string|callable|Authorizer $authorizer The authorizer to resolve
     * @return Authorizer The resolved authorizer instance
     */
    protected function resolveAuthorizer($authorizer): Authorizer
    {
        if (is_string($authorizer)) {
            return $this->container->make($authorizer);
        }

        if ($authorizer instanceof Authorizer) {
            return $authorizer;
        }

        throw new \InvalidArgumentException(
            "Could not resolve authorizer. Must be a class name, Authorizer instance, or callable that returns an Authorizer."
        );
    }
}
