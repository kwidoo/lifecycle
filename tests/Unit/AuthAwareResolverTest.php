<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Resolvers\AuthAwareResolver;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class AuthAwareResolverTest extends TestCase
{
    /** @var Container|MockInterface */
    private $container;

    /** @var AuthFactory|MockInterface */
    private $authFactory;

    /** @var Guard|MockInterface */
    private $guard;

    /** @var Authorizer|MockInterface */
    private $defaultAuthorizer;

    /** @var Authorizer|MockInterface */
    private $authenticatedAuthorizer;

    /** @var Authorizer|MockInterface */
    private $unauthenticatedAuthorizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);
        $this->authFactory = Mockery::mock(AuthFactory::class);
        $this->guard = Mockery::mock(Guard::class);
        $this->defaultAuthorizer = Mockery::mock(Authorizer::class);
        $this->authenticatedAuthorizer = Mockery::mock(Authorizer::class);
        $this->unauthenticatedAuthorizer = Mockery::mock(Authorizer::class);

        $this->authFactory->shouldReceive('guard')
            ->withNoArgs()
            ->andReturn($this->guard);
    }

    public function testResolvesAuthorizerForResourceAndActionWithExactConfig()
    {
        // Mock config lookup for specific resource/action
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.user.update')
            ->andReturn('DefaultAuthorizer');

        $this->container->shouldReceive('make')
            ->with('DefaultAuthorizer')
            ->andReturn($this->defaultAuthorizer);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $authorizer = $resolver->resolveForResourceAndAction('user', 'update');

        $this->assertSame($this->defaultAuthorizer, $authorizer);
    }

    public function testFallsBackToResourceDefaultAuthorizer()
    {
        // Mock config lookup for specific resource/action (not found)
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.product.delete')
            ->andReturnNull();

        // Mock config lookup for resource default
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.product.default')
            ->andReturn('ProductAuthorizer');

        $this->container->shouldReceive('make')
            ->with('ProductAuthorizer')
            ->andReturn($this->defaultAuthorizer);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $authorizer = $resolver->resolveForResourceAndAction('product', 'delete');

        $this->assertSame($this->defaultAuthorizer, $authorizer);
    }

    public function testFallsBackToGlobalDefaultAuthorizer()
    {
        // Mock config lookups that return null for specific and resource defaults
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.comment.create')
            ->andReturnNull();

        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.comment.default')
            ->andReturnNull();

        // Mock global default config
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.default')
            ->andReturn('GlobalDefaultAuthorizer');

        $this->container->shouldReceive('make')
            ->with('GlobalDefaultAuthorizer')
            ->andReturn($this->defaultAuthorizer);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $authorizer = $resolver->resolveForResourceAndAction('comment', 'create');

        $this->assertSame($this->defaultAuthorizer, $authorizer);
    }

    public function testSelectsAuthenticatedAuthorizerWhenUserIsAuthenticated()
    {
        // Set up authentication check to return true (user is logged in)
        $this->guard->shouldReceive('check')
            ->andReturn(true);

        // Mock config that returns array with authenticated/unauthenticated variants
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.post.edit')
            ->andReturn([
                'authenticated' => 'AuthenticatedAuthorizer',
                'unauthenticated' => 'UnauthenticatedAuthorizer'
            ]);

        $this->container->shouldReceive('make')
            ->with('AuthenticatedAuthorizer')
            ->andReturn($this->authenticatedAuthorizer);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $authorizer = $resolver->resolveForResourceAndAction('post', 'edit');

        $this->assertSame($this->authenticatedAuthorizer, $authorizer);
    }

    public function testSelectsUnauthenticatedAuthorizerWhenUserIsNotAuthenticated()
    {
        // Set up authentication check to return false (user is not logged in)
        $this->guard->shouldReceive('check')
            ->andReturn(false);

        // Mock config that returns array with authenticated/unauthenticated variants
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.post.edit')
            ->andReturn([
                'authenticated' => 'AuthenticatedAuthorizer',
                'unauthenticated' => 'UnauthenticatedAuthorizer'
            ]);

        $this->container->shouldReceive('make')
            ->with('UnauthenticatedAuthorizer')
            ->andReturn($this->unauthenticatedAuthorizer);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $authorizer = $resolver->resolveForResourceAndAction('post', 'edit');

        $this->assertSame($this->unauthenticatedAuthorizer, $authorizer);
    }

    public function testUsesSpecifiedAuthGuard()
    {
        // Set up a specific guard
        $adminGuard = Mockery::mock(Guard::class);
        $adminGuard->shouldReceive('check')
            ->andReturn(true);

        $this->authFactory->shouldReceive('guard')
            ->with('admin')
            ->andReturn($adminGuard);

        // Mock config with a specific guard
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.admin.action')
            ->andReturn([
                'authenticated' => 'AdminAuthorizer',
                'unauthenticated' => 'GuestAuthorizer',
                'guard' => 'admin'
            ]);

        $this->container->shouldReceive('make')
            ->with('AdminAuthorizer')
            ->andReturn($this->authenticatedAuthorizer);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $authorizer = $resolver->resolveForResourceAndAction('admin', 'action');

        $this->assertSame($this->authenticatedAuthorizer, $authorizer);
    }

    public function testThrowsExceptionForInvalidAuthorizer()
    {
        config()->shouldReceive('get')
            ->with('lifecycle.authorizers.invalid.test')
            ->andReturn(new \stdClass()); // Return a non-string, non-Authorizer

        $this->expectException(\InvalidArgumentException::class);

        $resolver = new AuthAwareResolver($this->authFactory, $this->container);
        $resolver->resolveForResourceAndAction('invalid', 'test');
    }
}
