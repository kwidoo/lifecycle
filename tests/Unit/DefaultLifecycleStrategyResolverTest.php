<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\DefaultLifecycleStrategyResolver;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Strategies\WithEvents;
use Kwidoo\Lifecycle\Strategies\WithLogging;
use Kwidoo\Lifecycle\Strategies\WithTransactions;
use Kwidoo\Lifecycle\Strategies\WithoutEvents;
use Kwidoo\Lifecycle\Strategies\WithoutLogging;
use Kwidoo\Lifecycle\Strategies\WithoutTransactions;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class DefaultLifecycleStrategyResolverTest extends TestCase
{
    /** @test */
    public function it_resolves_strategies_with_events_enabled()
    {
        // Arrange
        $withEvents = Mockery::mock(WithEvents::class);
        $withoutEvents = Mockery::mock(WithoutEvents::class);
        $loggingStrategy = Mockery::mock(LoggingStrategy::class);
        $transactionStrategy = Mockery::mock(TransactionStrategy::class);

        $resolver = new DefaultLifecycleStrategyResolver(
            eventableStrategies: [
                true => $withEvents,
                false => $withoutEvents,
            ],
            loggingStrategies: [
                true => $loggingStrategy,
                false => $loggingStrategy,
            ],
            transactionStrategies: [
                true => $transactionStrategy,
                false => $transactionStrategy,
            ]
        );

        $options = new LifecycleOptionsData();
        $options->eventsEnabled = true;

        // Act
        $strategies = $resolver->resolve($options);

        // Assert
        $this->assertInstanceOf(LifecycleStrategies::class, $strategies);
        $this->assertSame($withEvents, $strategies->eventable);
    }

    /** @test */
    public function it_resolves_strategies_with_events_disabled()
    {
        // Arrange
        $withEvents = Mockery::mock(WithEvents::class);
        $withoutEvents = Mockery::mock(WithoutEvents::class);
        $loggingStrategy = Mockery::mock(LoggingStrategy::class);
        $transactionStrategy = Mockery::mock(TransactionStrategy::class);

        $resolver = new DefaultLifecycleStrategyResolver(
            eventableStrategies: [
                true => $withEvents,
                false => $withoutEvents,
            ],
            loggingStrategies: [
                true => $loggingStrategy,
                false => $loggingStrategy,
            ],
            transactionStrategies: [
                true => $transactionStrategy,
                false => $transactionStrategy,
            ]
        );

        $options = new LifecycleOptionsData();
        $options->eventsEnabled = false;

        // Act
        $strategies = $resolver->resolve($options);

        // Assert
        $this->assertInstanceOf(LifecycleStrategies::class, $strategies);
        $this->assertSame($withoutEvents, $strategies->eventable);
    }

    /** @test */
    public function it_resolves_logging_strategies()
    {
        // Arrange
        $eventableStrategy = Mockery::mock(EventableStrategy::class);
        $withLogging = Mockery::mock(WithLogging::class);
        $withoutLogging = Mockery::mock(WithoutLogging::class);
        $transactionStrategy = Mockery::mock(TransactionStrategy::class);

        $resolver = new DefaultLifecycleStrategyResolver(
            eventableStrategies: [
                true => $eventableStrategy,
                false => $eventableStrategy,
            ],
            loggingStrategies: [
                true => $withLogging,
                false => $withoutLogging,
            ],
            transactionStrategies: [
                true => $transactionStrategy,
                false => $transactionStrategy,
            ]
        );

        // Test with logging enabled
        $optionsWithLogging = new LifecycleOptionsData();
        $optionsWithLogging->loggingEnabled = true;

        $strategies1 = $resolver->resolve($optionsWithLogging);
        $this->assertSame($withLogging, $strategies1->loggable);

        // Test with logging disabled
        $optionsWithoutLogging = new LifecycleOptionsData();
        $optionsWithoutLogging->loggingEnabled = false;

        $strategies2 = $resolver->resolve($optionsWithoutLogging);
        $this->assertSame($withoutLogging, $strategies2->loggable);
    }

    /** @test */
    public function it_resolves_transaction_strategies()
    {
        // Arrange
        $eventableStrategy = Mockery::mock(EventableStrategy::class);
        $loggingStrategy = Mockery::mock(LoggingStrategy::class);
        $withTransactions = Mockery::mock(WithTransactions::class);
        $withoutTransactions = Mockery::mock(WithoutTransactions::class);

        $resolver = new DefaultLifecycleStrategyResolver(
            eventableStrategies: [
                true => $eventableStrategy,
                false => $eventableStrategy,
            ],
            loggingStrategies: [
                true => $loggingStrategy,
                false => $loggingStrategy,
            ],
            transactionStrategies: [
                true => $withTransactions,
                false => $withoutTransactions,
            ]
        );

        // Test with transactions enabled
        $optionsWithTrx = new LifecycleOptionsData();
        $optionsWithTrx->trxEnabled = true;

        $strategies1 = $resolver->resolve($optionsWithTrx);
        $this->assertSame($withTransactions, $strategies1->transactional);

        // Test with transactions disabled
        $optionsWithoutTrx = new LifecycleOptionsData();
        $optionsWithoutTrx->trxEnabled = false;

        $strategies2 = $resolver->resolve($optionsWithoutTrx);
        $this->assertSame($withoutTransactions, $strategies2->transactional);
    }
}
