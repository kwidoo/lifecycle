<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Resolvers\DefaultStrategyResolver;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class DefaultStrategyResolverTest extends TestCase
{
    /** @var LifecycleStrategyResolver|MockInterface */
    private $legacyResolver;

    /** @var LifecycleStrategies|MockInterface */
    private $strategies;

    protected function setUp(): void
    {
        parent::setUp();

        $this->legacyResolver = Mockery::mock(LifecycleStrategyResolver::class);
        $this->strategies = Mockery::mock(LifecycleStrategies::class);
    }

    public function testDelegatesResolutionToLegacyResolver()
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

        // The legacy resolver should receive the options and return strategies
        $this->legacyResolver->shouldReceive('resolve')
            ->with($options)
            ->once()
            ->andReturn($this->strategies);

        // Create adapter and resolve strategies
        $adapter = new DefaultStrategyResolver($this->legacyResolver);
        $result = $adapter->resolve($options);

        // Ensure the returned strategies are the ones from the legacy resolver
        $this->assertSame($this->strategies, $result);
    }
}
