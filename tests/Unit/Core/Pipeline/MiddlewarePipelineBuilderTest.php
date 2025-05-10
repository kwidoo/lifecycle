<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Core\Pipeline;

use Kwidoo\Lifecycle\Contracts\Strategies\ErrorStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\EventStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LogStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\RetryStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Core\Pipeline\MiddlewarePipelineBuilder;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Middleware\AuthorizationMiddleware;
use Kwidoo\Lifecycle\Middleware\ErrorCatcherMiddleware;
use Kwidoo\Lifecycle\Middleware\EventsMiddleware;
use Kwidoo\Lifecycle\Middleware\FinalizationMiddleware;
use Kwidoo\Lifecycle\Middleware\LoggingMiddleware;
use Kwidoo\Lifecycle\Middleware\RetryMiddleware;
use Kwidoo\Lifecycle\Middleware\TransactionsMiddleware;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class MiddlewarePipelineBuilderTest extends TestCase
{
    private ErrorStrategy $errorStrategy;
    private EventStrategy $eventStrategy;
    private LogStrategy $logStrategy;
    private RetryStrategy $retryStrategy;
    private TransactionStrategy $transactionStrategy;
    private MiddlewarePipelineBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->errorStrategy = Mockery::mock(ErrorStrategy::class);
        $this->eventStrategy = Mockery::mock(EventStrategy::class);
        $this->logStrategy = Mockery::mock(LogStrategy::class);
        $this->retryStrategy = Mockery::mock(RetryStrategy::class);
        $this->transactionStrategy = Mockery::mock(TransactionStrategy::class);

        $this->builder = new MiddlewarePipelineBuilder(
            $this->errorStrategy,
            $this->eventStrategy,
            $this->logStrategy,
            $this->retryStrategy,
            $this->transactionStrategy
        );
    }

    /** @test */
    public function it_builds_complete_middleware_pipeline_with_default_options()
    {
        // When
        $options = new LifecycleOptionsData();
        $middlewares = $this->builder->build($options);

        // Then
        $this->assertCount(6, $middlewares);
        $this->assertInstanceOf(ErrorCatcherMiddleware::class, $middlewares[0]);
        $this->assertInstanceOf(RetryMiddleware::class, $middlewares[1]);
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middlewares[2]);
        $this->assertInstanceOf(TransactionsMiddleware::class, $middlewares[3]);
        $this->assertInstanceOf(EventsMiddleware::class, $middlewares[4]);
        $this->assertInstanceOf(LoggingMiddleware::class, $middlewares[5]);
    }

    /** @test */
    public function it_excludes_auth_middleware_when_auth_disabled()
    {
        // When
        $options = new LifecycleOptionsData()->withoutAuth();
        $middlewares = $this->builder->build($options);

        // Then
        $authMiddlewares = array_filter($middlewares, function ($middleware) {
            return $middleware instanceof AuthorizationMiddleware;
        });

        $this->assertEmpty($authMiddlewares);
        $this->assertInstanceOf(ErrorCatcherMiddleware::class, $middlewares[0]);
        $this->assertInstanceOf(FinalizationMiddleware::class, $middlewares[count($middlewares) - 1]);
    }

    /** @test */
    public function it_excludes_transaction_middleware_when_trx_disabled()
    {
        // When
        $options = new LifecycleOptionsData()->withoutTrx();
        $middlewares = $this->builder->build($options);

        // Then
        $trxMiddlewares = array_filter($middlewares, function ($middleware) {
            return $middleware instanceof TransactionsMiddleware;
        });

        $this->assertEmpty($trxMiddlewares);
    }

    /** @test */
    public function it_excludes_events_middleware_when_events_disabled()
    {
        // When
        $options = new LifecycleOptionsData()->withoutEvents();
        $middlewares = $this->builder->build($options);

        // Then
        $eventMiddlewares = array_filter($middlewares, function ($middleware) {
            return $middleware instanceof EventsMiddleware;
        });

        $this->assertEmpty($eventMiddlewares);
    }

    /** @test */
    public function it_excludes_logging_middleware_when_logging_disabled()
    {
        // When
        $options = new LifecycleOptionsData()->withoutLogging();
        $middlewares = $this->builder->build($options);

        // Then
        $logMiddlewares = array_filter($middlewares, function ($middleware) {
            return $middleware instanceof LoggingMiddleware;
        });

        $this->assertEmpty($logMiddlewares);
    }

    /** @test */
    public function it_excludes_retry_middleware_when_retry_disabled()
    {
        // When
        $options = new LifecycleOptionsData()->withoutRetry();
        $middlewares = $this->builder->build($options);

        // Then
        $retryMiddlewares = array_filter($middlewares, function ($middleware) {
            return $middleware instanceof RetryMiddleware;
        });

        $this->assertEmpty($retryMiddlewares);
    }

    /** @test */
    public function it_adds_error_catcher_first_and_finalization_last()
    {
        // When
        $options = new LifecycleOptionsData();
        $middlewares = $this->builder->build($options);

        // Then
        $this->assertInstanceOf(ErrorCatcherMiddleware::class, $middlewares[0]);
        $this->assertInstanceOf(FinalizationMiddleware::class, $middlewares[count($middlewares) - 1]);
    }
}
