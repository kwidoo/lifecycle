<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Contracts\Resolvers\StrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Resolvers\DefaultLifecycleResolver;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class DefaultLifecycleResolverTest extends TestCase
{
    /** @var Container|MockInterface */
    private $container;

    /** @var StrategyResolver|MockInterface */
    private $strategyResolver;

    /** @var LifecycleStrategies|MockInterface */
    private $strategies;

    /** @var Lifecycle|MockInterface */
    private $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);
        $this->strategyResolver = Mockery::mock(StrategyResolver::class);
        $this->strategies = Mockery::mock(LifecycleStrategies::class);
        $this->lifecycle = Mockery::mock(Lifecycle::class);
    }

    public function testResolvesLifecycleImplementationUsingStrategies()
    {
        $options = new LifecycleOptionsData(
            'test',
            'action',
            ['context'],
            true, // events enabled
            true, // transactions enabled
            true, // logging enabled
            true  // retry enabled
        );

        // Mock strategy resolution
        $this->strategyResolver->shouldReceive('resolve')
            ->with($options)
            ->andReturn($this->strategies);

        // Mock lifecycle class from config
        config()->shouldReceive('get')
            ->with('lifecycle.implementations.lifecycle', \Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle::class)
            ->andReturn('CustomLifecycle');

        // Mock container resolution of the lifecycle implementation
        $this->container->shouldReceive('make')
            ->with('CustomLifecycle', [
                'strategies' => $this->strategies,
                'options' => $options
            ])
            ->andReturn($this->lifecycle);

        $resolver = new DefaultLifecycleResolver($this->container, $this->strategyResolver);
        $result = $resolver->resolve($options);

        $this->assertSame($this->lifecycle, $result);
    }

    public function testFallsBackToDefaultLifecycleClass()
    {
        $options = new LifecycleOptionsData(
            'test',
            'action',
            ['context'],
            true,
            true,
            true,
            true
        );

        // Mock strategy resolution
        $this->strategyResolver->shouldReceive('resolve')
            ->with($options)
            ->andReturn($this->strategies);

        // Mock config returning null (forcing default)
        config()->shouldReceive('get')
            ->with('lifecycle.implementations.lifecycle', \Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle::class)
            ->andReturn(\Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle::class);

        // Mock container resolution
        $this->container->shouldReceive('make')
            ->with(\Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle::class, [
                'strategies' => $this->strategies,
                'options' => $options
            ])
            ->andReturn($this->lifecycle);

        $resolver = new DefaultLifecycleResolver($this->container, $this->strategyResolver);
        $result = $resolver->resolve($options);

        $this->assertSame($this->lifecycle, $result);
    }
}
