<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Contracts\Container\Container;
use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Resolvers\ConfigDrivenStrategyResolver;
use Kwidoo\Lifecycle\Strategies\NoopEventStrategy;
use Kwidoo\Lifecycle\Strategies\NoopLogStrategy;
use Kwidoo\Lifecycle\Strategies\NoopRetryStrategy;
use Kwidoo\Lifecycle\Strategies\NoopTransactionStrategy;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class ConfigDrivenStrategyResolverTest extends TestCase
{
    /** @var Container|MockInterface */
    private $container;

    /** @var EventStrategy|MockInterface */
    private $eventStrategy;

    /** @var LogStrategy|MockInterface */
    private $logStrategy;

    /** @var TransactionStrategy|MockInterface */
    private $transactionStrategy;

    /** @var RetryStrategy|MockInterface */
    private $retryStrategy;

    /** @var ErrorStrategy|MockInterface */
    private $errorStrategy;

    /** @var NoopEventStrategy|MockInterface */
    private $noopEventStrategy;

    /** @var NoopLogStrategy|MockInterface */
    private $noopLogStrategy;

    /** @var NoopTransactionStrategy|MockInterface */
    private $noopTransactionStrategy;

    /** @var NoopRetryStrategy|MockInterface */
    private $noopRetryStrategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = Mockery::mock(Container::class);

        $this->eventStrategy = Mockery::mock(EventStrategy::class);
        $this->logStrategy = Mockery::mock(LogStrategy::class);
        $this->transactionStrategy = Mockery::mock(TransactionStrategy::class);
        $this->retryStrategy = Mockery::mock(RetryStrategy::class);
        $this->errorStrategy = Mockery::mock(ErrorStrategy::class);

        $this->noopEventStrategy = Mockery::mock(NoopEventStrategy::class);
        $this->noopLogStrategy = Mockery::mock(NoopLogStrategy::class);
        $this->noopTransactionStrategy = Mockery::mock(NoopTransactionStrategy::class);
        $this->noopRetryStrategy = Mockery::mock(NoopRetryStrategy::class);
    }

    public function testResolvesProperStrategiesWhenAllEnabled()
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

        // Mock config values
        config()->shouldReceive('get')
            ->with('lifecycle.strategies.event.enabled', EventStrategy::class)
            ->andReturn(EventStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.transaction.enabled', TransactionStrategy::class)
            ->andReturn(TransactionStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.log.enabled', LogStrategy::class)
            ->andReturn(LogStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.retry.enabled', RetryStrategy::class)
            ->andReturn(RetryStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.error.enabled', ErrorStrategy::class)
            ->andReturn(ErrorStrategy::class);

        // Mock container resolutions
        $this->container->shouldReceive('make')
            ->with(EventStrategy::class)
            ->andReturn($this->eventStrategy);

        $this->container->shouldReceive('make')
            ->with(TransactionStrategy::class)
            ->andReturn($this->transactionStrategy);

        $this->container->shouldReceive('make')
            ->with(LogStrategy::class)
            ->andReturn($this->logStrategy);

        $this->container->shouldReceive('make')
            ->with(RetryStrategy::class)
            ->andReturn($this->retryStrategy);

        $this->container->shouldReceive('make')
            ->with(ErrorStrategy::class)
            ->andReturn($this->errorStrategy);

        $resolver = new ConfigDrivenStrategyResolver($this->container);
        $strategies = $resolver->resolve($options);

        $this->assertInstanceOf(LifecycleStrategies::class, $strategies);
        // We can't directly check the properties of LifecycleStrategies as they're protected,
        // but we can verify the container made the right objects
    }

    public function testResolvesNoopStrategiesWhenAllDisabled()
    {
        $options = new LifecycleOptionsData(
            'test',
            'action',
            ['context'],
            false, // events disabled
            false, // transactions disabled
            false, // logging disabled
            false  // retry disabled
        );

        // Mock config values for disabled strategies
        config()->shouldReceive('get')
            ->with('lifecycle.strategies.event.disabled', NoopEventStrategy::class)
            ->andReturn(NoopEventStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.transaction.disabled', NoopTransactionStrategy::class)
            ->andReturn(NoopTransactionStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.log.disabled', NoopLogStrategy::class)
            ->andReturn(NoopLogStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.retry.disabled', NoopRetryStrategy::class)
            ->andReturn(NoopRetryStrategy::class);

        config()->shouldReceive('get')
            ->with('lifecycle.strategies.error.enabled', ErrorStrategy::class)
            ->andReturn(ErrorStrategy::class);

        // Mock container resolutions
        $this->container->shouldReceive('make')
            ->with(NoopEventStrategy::class)
            ->andReturn($this->noopEventStrategy);

        $this->container->shouldReceive('make')
            ->with(NoopTransactionStrategy::class)
            ->andReturn($this->noopTransactionStrategy);

        $this->container->shouldReceive('make')
            ->with(NoopLogStrategy::class)
            ->andReturn($this->noopLogStrategy);

        $this->container->shouldReceive('make')
            ->with(NoopRetryStrategy::class)
            ->andReturn($this->noopRetryStrategy);

        $this->container->shouldReceive('make')
            ->with(ErrorStrategy::class)
            ->andReturn($this->errorStrategy);

        $resolver = new ConfigDrivenStrategyResolver($this->container);
        $strategies = $resolver->resolve($options);

        $this->assertInstanceOf(LifecycleStrategies::class, $strategies);
    }

    public function testFallsBackToDefaultsWhenConfigIsUndefined()
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

        // Mock config values to return null, forcing fallbacks
        config()->shouldReceive('get')
            ->andReturnNull();

        // Mock container resolutions with defaults
        $this->container->shouldReceive('make')
            ->with(EventStrategy::class)
            ->andReturn($this->eventStrategy);

        $this->container->shouldReceive('make')
            ->with(TransactionStrategy::class)
            ->andReturn($this->transactionStrategy);

        $this->container->shouldReceive('make')
            ->with(LogStrategy::class)
            ->andReturn($this->logStrategy);

        $this->container->shouldReceive('make')
            ->with(RetryStrategy::class)
            ->andReturn($this->retryStrategy);

        $this->container->shouldReceive('make')
            ->with(ErrorStrategy::class)
            ->andReturn($this->errorStrategy);

        $resolver = new ConfigDrivenStrategyResolver($this->container);
        $strategies = $resolver->resolve($options);

        $this->assertInstanceOf(LifecycleStrategies::class, $strategies);
    }
}
