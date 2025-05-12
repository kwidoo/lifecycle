<?php

namespace Kwidoo\Lifecycle\Tests\Feature\CQRS;

use Illuminate\Pipeline\Pipeline;
use Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory;
use Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver;
use Kwidoo\Lifecycle\Core\Engine\DefaultLifecycle;
use Kwidoo\Lifecycle\CQRS\CQRSService;
use Kwidoo\Lifecycle\CQRS\Contracts\Command;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class DefaultLifecycleCQRSTest extends TestCase
{
    /** @var MockInterface|AuthorizerFactory */
    protected $authorizerFactory;

    /** @var MockInterface|LifecycleStrategyResolver */
    protected $strategyResolver;

    /** @var MockInterface|Pipeline */
    protected $pipeline;

    /** @var MockInterface|LifecycleMiddlewareFactory */
    protected $middlewareFactory;

    /** @var MockInterface|CQRSService */
    protected $cqrsService;

    /** @var DefaultLifecycle */
    protected $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizerFactory = Mockery::mock(AuthorizerFactory::class);
        $this->strategyResolver = Mockery::mock(LifecycleStrategyResolver::class);
        $this->pipeline = Mockery::mock(Pipeline::class);
        $this->middlewareFactory = Mockery::mock(LifecycleMiddlewareFactory::class);
        $this->cqrsService = Mockery::mock(CQRSService::class);

        $this->lifecycle = new DefaultLifecycle(
            $this->authorizerFactory,
            $this->strategyResolver,
            $this->pipeline,
            $this->middlewareFactory,
            $this->cqrsService
        );
    }

    /** @test */
    public function it_handles_command_mode_with_automatic_command_creation()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'register',
            'user',
            ['name' => 'John Doe', 'email' => 'john@example.com']
        );

        $options = (new LifecycleOptionsData())->useCQRS();
        $middlewares = ['middleware1', 'middleware2'];
        $expectedResult = ['success' => true, 'userId' => '123'];

        // Setup authorization mocking
        $this->mockAuthorization($contextData);

        // Setup middleware factory mocking
        $this->middlewareFactory->shouldReceive('forOptions')
            ->with($options)
            ->once()
            ->andReturn($middlewares);

        // Setup pipeline mocking
        $this->pipeline->shouldReceive('send')
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('through')
            ->with($middlewares)
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('then')
            ->once()
            ->andReturnUsing(function ($callback) use ($expectedResult) {
                // Simulate pipeline execution
                return $callback(Mockery::mock());
            });

        // Setup CQRS service mocking
        $this->cqrsService->shouldReceive('handleCommand')
            ->once()
            ->andReturn($expectedResult);

        // Act
        $result = $this->lifecycle->run(
            $contextData,
            fn() => null, // Callback should not be used
            $options
        );

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    /** @test */
    public function it_falls_back_to_callback_when_automatic_command_creation_fails()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'register',
            'user',
            ['name' => 'John Doe', 'email' => 'john@example.com']
        );

        $options = (new LifecycleOptionsData())->useCQRS();
        $middlewares = ['middleware1', 'middleware2'];
        $expectedResult = ['success' => true, 'userId' => '123'];
        $callbackExecuted = false;

        // Setup authorization mocking
        $this->mockAuthorization($contextData);

        // Setup middleware factory mocking
        $this->middlewareFactory->shouldReceive('forOptions')
            ->with($options)
            ->once()
            ->andReturn($middlewares);

        // Setup pipeline mocking
        $this->pipeline->shouldReceive('send')
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('through')
            ->with($middlewares)
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('then')
            ->once()
            ->andReturnUsing(function ($callback) use ($expectedResult, &$callbackExecuted) {
                // Simulate pipeline execution
                return $callback(Mockery::mock());
            });

        // Setup CQRS service mocking to throw an exception
        $this->cqrsService->shouldReceive('handleCommand')
            ->once()
            ->andThrow(new \InvalidArgumentException('No CQRS mapping found'));

        // Act
        $result = $this->lifecycle->run(
            $contextData,
            function ($data) use ($expectedResult, &$callbackExecuted) {
                $callbackExecuted = true;
                return $expectedResult;
            },
            $options
        );

        // Assert
        $this->assertEquals($expectedResult, $result);
        $this->assertTrue($callbackExecuted, 'Callback should have been executed');
    }

    /** @test */
    public function it_handles_query_mode_with_automatic_query_execution()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'list',
            'user',
            ['active' => true]
        );

        $options = (new LifecycleOptionsData())->asQuery();
        $queryMiddlewares = ['middleware1']; // Lighter middleware stack
        $expectedResults = [
            ['id' => '1', 'name' => 'John Doe'],
            ['id' => '2', 'name' => 'Jane Smith']
        ];

        // Setup authorization mocking
        $this->mockAuthorization($contextData);

        // Setup middleware factory mocking with query-specific middleware
        $this->middlewareFactory->shouldReceive('forQueryOptions')
            ->once()
            ->andReturn($queryMiddlewares);

        // Setup pipeline mocking
        $this->pipeline->shouldReceive('send')
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('through')
            ->with($queryMiddlewares)
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('then')
            ->once()
            ->andReturnUsing(function ($callback) use ($expectedResults) {
                // Simulate pipeline execution
                return $callback(Mockery::mock());
            });

        // Setup CQRS service mocking for query
        $this->cqrsService->shouldReceive('query')
            ->once()
            ->andReturn($expectedResults);

        // Act
        $result = $this->lifecycle->run(
            $contextData,
            fn() => null, // Callback should not be used
            $options
        );

        // Assert
        $this->assertEquals($expectedResults, $result);
    }

    /** @test */
    public function it_falls_back_to_callback_when_automatic_query_fails()
    {
        // Arrange
        $contextData = new LifecycleContextData(
            'list',
            'user',
            ['active' => true]
        );

        $options = (new LifecycleOptionsData())->asQuery();
        $queryMiddlewares = ['middleware1']; // Lighter middleware stack
        $expectedResults = [
            ['id' => '1', 'name' => 'John Doe'],
            ['id' => '2', 'name' => 'Jane Smith']
        ];
        $callbackExecuted = false;

        // Setup authorization mocking
        $this->mockAuthorization($contextData);

        // Setup middleware factory mocking with query-specific middleware
        $this->middlewareFactory->shouldReceive('forQueryOptions')
            ->once()
            ->andReturn($queryMiddlewares);

        // Setup pipeline mocking
        $this->pipeline->shouldReceive('send')
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('through')
            ->with($queryMiddlewares)
            ->once()
            ->andReturnSelf();

        $this->pipeline->shouldReceive('then')
            ->once()
            ->andReturnUsing(function ($callback) use ($expectedResults) {
                // Simulate pipeline execution
                return $callback(Mockery::mock());
            });

        // Setup CQRS service mocking to throw an exception
        $this->cqrsService->shouldReceive('query')
            ->once()
            ->andThrow(new \InvalidArgumentException('No read model repository found'));

        // Act
        $result = $this->lifecycle->run(
            $contextData,
            function ($data) use ($expectedResults, &$callbackExecuted) {
                $callbackExecuted = true;
                return $expectedResults;
            },
            $options
        );

        // Assert
        $this->assertEquals($expectedResults, $result);
        $this->assertTrue($callbackExecuted, 'Callback should have been executed');
    }

    /**
     * Helper method to mock authorization
     */
    private function mockAuthorization(LifecycleContextData $contextData)
    {
        $mockAuthorizer = Mockery::mock('Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer');

        $this->authorizerFactory->shouldReceive('resolve')
            ->with($contextData->resource)
            ->andReturn($mockAuthorizer);

        $mockAuthorizer->shouldReceive('authorize')
            ->with($contextData->action, $contextData->context)
            ->andReturnNull();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
