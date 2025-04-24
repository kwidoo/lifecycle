<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Contracts\Strategies\EventableStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\LoggingStrategy;
use Kwidoo\Lifecycle\Contracts\Strategies\TransactionStrategy;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Lifecycle\DefaultLifecycle;
use Kwidoo\Lifecycle\Lifecycle\LifecycleStrategies;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Spatie\LaravelData\Data;

class TestRequestData extends Data
{
    public function __construct(
        public int $id = 1,
        public string $name = 'Test'
    ) {
    }
}

class DefaultLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_executes_operation_with_default_options()
    {
        // Arrange
        $resolver = Mockery::mock(LifecycleStrategyResolver::class);
        $authFactory = Mockery::mock(AuthorizerFactory::class);
        $authorizer = Mockery::mock(Authorizer::class);

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

        // Setup mocks for strategies
        $resolver->shouldReceive('resolve')
            ->once()
            ->with(Mockery::type(LifecycleOptionsData::class))
            ->andReturn($strategies);

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

        $lifecycle = new DefaultLifecycle($authFactory, $resolver);
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

    /** @test */
    public function it_executes_operation_with_auth_disabled()
    {
        // Arrange
        $resolver = Mockery::mock(LifecycleStrategyResolver::class);
        $authFactory = Mockery::mock(AuthorizerFactory::class);

        $eventable = Mockery::mock(EventableStrategy::class);
        $loggable = Mockery::mock(LoggingStrategy::class);
        $transactional = Mockery::mock(TransactionStrategy::class);

        $strategies = new LifecycleStrategies($eventable, $transactional, $loggable);

        // Auth should not be called when disabled
        $authFactory->shouldNotReceive('resolve');

        // Setup mocks for strategies
        $resolver->shouldReceive('resolve')
            ->once()
            ->with(Mockery::on(function ($options) {
                return $options->authEnabled === false;
            }))
            ->andReturn($strategies);

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

        $lifecycle = new DefaultLifecycle($authFactory, $resolver);
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

    /** @test */
    public function it_handles_errors_and_dispatches_error_events()
    {
        // Arrange
        $resolver = Mockery::mock(LifecycleStrategyResolver::class);
        $authFactory = Mockery::mock(AuthorizerFactory::class);
        $authorizer = Mockery::mock(Authorizer::class);

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

        // Setup strategy mocks
        $resolver->shouldReceive('resolve')
            ->once()
            ->andReturn($strategies);

        // An exception will be thrown during eventable execution
        $exception = new \Exception('Test error');
        $eventable->shouldReceive('executeEvents')
            ->once()
            ->andThrow($exception);

        // Error should be reported to strategies
        $eventable->shouldReceive('dispatchError')
            ->once()
            ->with(Mockery::type(LifecycleData::class));

        $loggable->shouldReceive('dispatchError')
            ->once()
            ->with(Mockery::type(LifecycleData::class));

        $lifecycle = new DefaultLifecycle($authFactory, $resolver);
        $data = new LifecycleData(
            action: 'delete',
            resource: 'User',
            context: $context
        );

        // Act & Assert
        $this->expectExceptionObject($exception);
        $lifecycle->run($data, fn() => 'should not reach here', new LifecycleOptionsData());
    }
}
