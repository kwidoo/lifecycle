<?php

namespace Kwidoo\Lifecycle\Factories;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Factories\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Features\Authorizer;
use Kwidoo\Lifecycle\Features\Authorizers\DefaultAuthorizer;

class DefaultAuthorizerFactory implements AuthorizerFactory
{
    /**
     * @var array<string, class-string<Authorizer>>
     */
    protected array $resourceAuthorizers;

    /**
     * Create a new authorizer factory instance
     *
     * @param Container $container The Laravel container
     */
    public function __construct(
        protected Container $container
    ) {
        $this->resourceAuthorizers = $this->loadAuthorizerMap();
    }

    /**
     * Resolve the appropriate authorizer for a given resource
     *
     * @param string $resource The resource name
     * @return Authorizer
     */
    public function resolve(string $resource): Authorizer
    {
        $authorizerClass = $this->resourceAuthorizers[$resource] ?? $this->resourceAuthorizers['default'];

        return $this->container->make($authorizerClass);
    }

    /**
     * Load the authorizer map from configuration
     *
     * @return array<string, class-string<Authorizer>>
     */
    protected function loadAuthorizerMap(): array
    {
        $configuredMap = config('lifecycle.authorizers', []);

        return array_merge([
            'default' => DefaultAuthorizer::class,
        ], $configuredMap);
    }
}
