<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Event;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;
use Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Middleware\ErrorCatcherMiddleware;
use Kwidoo\Lifecycle\Middleware\WithEventsMiddleware;
use Kwidoo\Lifecycle\Middleware\WithLoggingMiddleware;
use Kwidoo\Lifecycle\Middleware\WithTransactionsMiddleware;
use Kwidoo\Lifecycle\Tests\Data\TestRequestData;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class DefaultLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    #[Test]
    public function test_executes_operation_with_default_options()
    {
        // Arrange
        $resolver = Mockery::mock(LifecycleStrategyResolver::class);
        $authFactory = Mockery::mock(AuthorizerFactory::class);
        $authorizer = Mockery::mock(Authorizer::class);
        $pipeline = $this->getMockPipeline();
        $middlewareFactory = Mockery::mock(LifecycleMiddlewareFactory::class);

        $eventable = Mockery::mock(EventableStrategy::class);
        $loggable = Mockery::mock(LoggingStrategy::class);
        $transactional = Mockery::mock(TransactionStrategy::class);

        $strategies = new LifecycleStrategies($eventable, $transactional, $loggable);

        // Setup mocks for authorization
        $context = new TestRequestData();
        $authFactory->shouldReceive('resolve')
            ->once()
            ->with('User')
            ->andReturn($authorizer);

        $authorizer->shouldReceive('authorize')
            ->once()
            ->with('create', $context);

        // Setup mocks for strategies - Now resolver.resolve is called from middleware factory
        // Remove the direct resolver.resolve expectation
        $middlewareFactory->shouldReceive('forOptions')
            ->once()
            ->with(Mockery::type(LifecycleOptionsData::class))
            ->andReturn([
                new ErrorCatcherMiddleware($strategies),
                new WithEventsMiddleware($eventable),
                new WithLoggingMiddleware($loggable),
                new WithTransactionsMiddleware($transactional),
            ]);

        // Setup strategy mocks
        $eventable->shouldReceive('executeEvents')
            ->once()
            ->andReturnUsing(function ($data, $callback) {
                return $callback();
            });

        $loggable->shouldReceive('executeLogging')
            ->once()
            ->andReturnUsing(function ($data, $callback) {
                return $callback();
            });

        $transactional->shouldReceive('executeTransactions')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $lifecycle = new DefaultLifecycle($authFactory, $resolver, $pipeline, $middlewareFactory);
        $data = new LifecycleData(
            action: 'create',
            resource: 'User',
            context: $context
        );

        $callbackCalled = false;
        $callback = function () use (&$callbackCalled) {
            $callbackCalled = true;
            return 'result';
        };

        // Act
        $result = $lifecycle->run($data, $callback, new LifecycleOptionsData());

        // Assert
        $this->assertEquals('result', $result);
        $this->assertTrue($callbackCalled, 'Callback should have been called');
    }

    #[Test]
    public function test_executes_operation_with_auth_disabled()
    {
        // Arrange
        $resolver = Mockery::mock(LifecycleStrategyResolver::class);
        $authFactory = Mockery::mock(AuthorizerFactory::class);
        $pipeline = $this->getMockPipeline();
        $middlewareFactory = Mockery::mock(LifecycleMiddlewareFactory::class);

        $eventable = Mockery::mock(EventableStrategy::class);
        $loggable = Mockery::mock(LoggingStrategy::class);
        $transactional = Mockery::mock(TransactionStrategy::class);

        $strategies = new LifecycleStrategies($eventable, $transactional, $loggable);

        // Auth should not be called when disabled
        $authFactory->shouldNotReceive('resolve');

        // Setup mocks for strategies - now handled by middleware factory
        $middlewareFactory->shouldReceive('forOptions')
            ->once()
            ->with(Mockery::on(function ($options) {
                return $options->authEnabled === false;
            }))
            ->andReturn([
                new ErrorCatcherMiddleware($strategies),
                new WithEventsMiddleware($eventable),
                new WithLoggingMiddleware($loggable),
                new WithTransactionsMiddleware($transactional),
            ]);

        // Setup strategy mocks
        $eventable->shouldReceive('executeEvents')
            ->once()
            ->andReturnUsing(function ($data, $callback) {
                return $callback();
            });

        $loggable->shouldReceive('executeLogging')
            ->once()
            ->andReturnUsing(function ($data, $callback) {
                return $callback();
            });

        $transactional->shouldReceive('executeTransactions')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $lifecycle = new DefaultLifecycle($authFactory, $resolver, $pipeline, $middlewareFactory);
        $data = new LifecycleData(
            action: 'update',
            resource: 'User',
            context: new TestRequestData(id: 1, name: 'Updated Name')
        );

        $options = new LifecycleOptionsData();
        $options->authEnabled = false;

        // Act
        $result = $lifecycle->run($data, fn() => 'custom result', $options);

        // Assert
        $this->assertEquals('custom result', $result);
    }

    #[Test]
    public function test_handles_errors_and_dispatches_error_events()
    {
        // Arrange
        $resolver = Mockery::mock(LifecycleStrategyResolver::class);
        $authFactory = Mockery::mock(AuthorizerFactory::class);
        $authorizer = Mockery::mock(Authorizer::class);
        $pipeline = $this->getMockPipeline();
        $middlewareFactory = Mockery::mock(LifecycleMiddlewareFactory::class);

        $eventable = Mockery::mock(EventableStrategy::class);
        $loggable = Mockery::mock(LoggingStrategy::class);
        $transactional = Mockery::mock(TransactionStrategy::class);

        $strategies = new LifecycleStrategies($eventable, $transactional, $loggable);

        // Setup mocks for authorization
        $context = new TestRequestData();
        $authFactory->shouldReceive('resolve')
            ->once()
            ->with('User')
            ->andReturn($authorizer);

        $authorizer->shouldReceive('authorize')
            ->once()
            ->with('delete', $context);

        // Setup middleware factory mock
        $middlewareFactory->shouldReceive('forOptions')
            ->once()
            ->with(Mockery::type(LifecycleOptionsData::class))
            ->andReturn([
                new ErrorCatcherMiddleware($strategies),
            ]);

        // Error should be reported to strategies when the exception bubbles up to the middleware
        $eventable->shouldReceive('dispatchError')
            ->once()
            ->with(Mockery::type(LifecycleData::class));

        $loggable->shouldReceive('dispatchError')
            ->once()
            ->with(Mockery::type(LifecycleData::class));

        $lifecycle = new DefaultLifecycle($authFactory, $resolver, $pipeline, $middlewareFactory);
        $data = new LifecycleData(
            action: 'delete',
            resource: 'User',
            context: $context
        );

        // Create an exception that will be thrown in the callback
        $exception = new \Exception('Test error');

        // Act & Assert
        $this->expectExceptionObject($exception);
        $lifecycle->run($data, function () use ($exception) {
            throw $exception;
        }, new LifecycleOptionsData());
    }

    private function getMockPipeline()
    {
        $pipeline = Mockery::mock(Pipeline::class);
        $middlewares = [];
        $passable = null;

        $pipeline->shouldReceive('send')->andReturnUsing(function ($data) use ($pipeline, &$passable) {
            $passable = $data;
            return $pipeline;
        });

        $pipeline->shouldReceive('through')->andReturnUsing(function ($stack) use ($pipeline, &$middlewares) {
            $middlewares = $stack;
            return $pipeline;
        });

        $pipeline->shouldReceive('then')->andReturnUsing(function ($destination) use (&$middlewares, &$passable) {
            $pipeline = function ($passable) use ($destination) {
                return $destination($passable);
            };

            // Process the middleware stack from right to left (last to first)
            foreach (array_reverse($middlewares) as $middleware) {
                $pipeline = function ($passable) use ($pipeline, $middleware) {
                    return $middleware->handle($passable, $pipeline);
                };
            }

            return $pipeline($passable);
        });

        return $pipeline;
    }
}
