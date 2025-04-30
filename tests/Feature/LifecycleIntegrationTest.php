<?php

namespace Kwidoo\Lifecycle\Tests\Feature;

use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Event;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Lifecycle;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleOptionsData;
use Kwidoo\Lifecycle\Factories\LifecycleMiddlewareFactory;
use Kwidoo\Lifecycle\Tests\Data\TestEntityData;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Attributes\Test;

class LifecycleIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the authorization to avoid errors since we're using data objects
        $this->mock('Kwidoo\Lifecycle\Contracts\Authorizers\AuthorizerFactory', function ($mock) {
            $mock->shouldReceive('resolve')->andReturn(
                $this->mock('Kwidoo\Lifecycle\Contracts\Authorizers\Authorizer', function ($auth) {
                    $auth->shouldReceive('authorize')->andReturn(null);
                })
            );
        });

        // Mock the logger to avoid serialization issues with Data objects
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->andReturn(null);
        $logger->shouldReceive('error')->andReturn(null);
        $logger->shouldReceive('debug')->andReturn(null);
        $logger->shouldReceive('warning')->andReturn(null);

        $logManager = Mockery::mock(LogManager::class);
        $logManager->shouldReceive('channel')->andReturn($logger);

        $this->app->instance(LogManager::class, $logManager);

        // Make sure the app can resolve the LifecycleMiddlewareFactory
        if (!$this->app->bound(LifecycleMiddlewareFactory::class)) {
            $this->app->bind(LifecycleMiddlewareFactory::class, function ($app) {
                return new LifecycleMiddlewareFactory(
                    $app->make('Kwidoo\Lifecycle\Contracts\Lifecycle\LifecycleStrategyResolver')
                );
            });
        }

        Event::fake();
    }

    #[Test]
    public function test_can_execute_a_complete_lifecycle_operation()
    {
        // Arrange
        $lifecycle = $this->app->make(Lifecycle::class);

        $testEntity = new TestEntityData();

        // Create lifecycle data with context
        $data = new LifecycleData(
            action: 'retrieve',
            resource: 'TestEntity',
            context: $testEntity
        );

        $options = new LifecycleOptionsData(
            eventsEnabled: true,
            loggingEnabled: true,
            trxEnabled: false
        );

        // Act
        $result = $lifecycle->run($data, fn() => $testEntity, $options);

        // Assert
        $this->assertEquals($testEntity, $result);
        Event::assertDispatched('before.TestEntity.retrieve');
        Event::assertDispatched('after.TestEntity.retrieve');
    }

    #[Test]
    public function test_handles_errors_properly_in_lifecycle()
    {
        // Arrange
        $lifecycle = $this->app->make(Lifecycle::class);
        $exception = new \Exception('Operation failed');

        // Create lifecycle data that will fail
        $data = new LifecycleData(
            action: 'update',
            resource: 'TestEntity',
            context: new TestEntityData(id: 1, name: 'Updated Name')
        );

        $options = new LifecycleOptionsData(
            eventsEnabled: true,
            loggingEnabled: true,
            trxEnabled: false  // Disable transactions to simplify the test
        );

        // Act & Assert
        try {
            $lifecycle->run($data, function () use ($exception) {
                throw $exception;
            }, $options);

            $this->fail('Exception was not thrown');
        } catch (\Exception $e) {
            $this->assertSame($exception, $e);

            // Verify events
            Event::assertDispatched('before.TestEntity.update');
            Event::assertDispatched('error.TestEntity.update');
            Event::assertNotDispatched('after.TestEntity.update');
        }
    }

    #[Test]
    public function test_supports_disabling_all_features()
    {
        // Arrange
        $lifecycle = $this->app->make(Lifecycle::class);

        $testResult = new TestEntityData(id: 99, name: 'Success Result');

        // Create lifecycle data with all features disabled
        $data = new LifecycleData(
            action: 'process',
            resource: 'TestEntity',
            context: new TestEntityData(id: 0, name: 'Test')
        );

        $options = new LifecycleOptionsData(
            authEnabled: false,
            eventsEnabled: false,
            loggingEnabled: false,
            trxEnabled: false
        );

        // Act
        $result = $lifecycle->run($data, fn() => $testResult, $options);

        // Assert
        $this->assertEquals($testResult, $result);
        Event::assertNotDispatched('before.TestEntity.process');
        Event::assertNotDispatched('after.TestEntity.process');
    }
}
