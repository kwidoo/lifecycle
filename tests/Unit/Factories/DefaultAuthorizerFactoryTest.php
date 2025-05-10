<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Factories;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Authorizers\DefaultAuthorizer;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Factories\DefaultAuthorizerFactory;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class DefaultAuthorizerFactoryTest extends TestCase
{
    /** @test */
    public function it_resolves_default_authorizer_for_unknown_resource()
    {
        // Given
        $container = Mockery::mock(Container::class);
        $defaultAuthorizer = Mockery::mock(Authorizer::class);

        $container->shouldReceive('make')
            ->once()
            ->with(DefaultAuthorizer::class)
            ->andReturn($defaultAuthorizer);

        $factory = new DefaultAuthorizerFactory($container);

        // When
        $result = $factory->resolve('unknown-resource');

        // Then
        $this->assertSame($defaultAuthorizer, $result);
    }

    /** @test */
    public function it_resolves_configured_authorizer_from_config()
    {
        // Given
        $container = Mockery::mock(Container::class);
        $customAuthorizer = Mockery::mock(Authorizer::class);
        $customAuthorizerClass = 'CustomAuthorizer';

        // Mock the config to return a custom authorizer for 'user' resource
        $this->app->config->set('lifecycle.authorizers', [
            'user' => $customAuthorizerClass,
        ]);

        $container->shouldReceive('make')
            ->once()
            ->with($customAuthorizerClass)
            ->andReturn($customAuthorizer);

        $factory = new DefaultAuthorizerFactory($container);

        // When
        $result = $factory->resolve('user');

        // Then
        $this->assertSame($customAuthorizer, $result);
    }
}
