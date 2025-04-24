<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Strategies;

use Exception;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Loggable;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Strategies\WithLogging;
use Kwidoo\Lifecycle\Tests\Data\TestLogData;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class WithLoggingTest extends TestCase
{
    /** @test */
    public function it_logs_before_and_after_execution()
    {
        // Arrange
        $loggable = $this->mockLoggable();
        $strategy = new WithLogging($loggable);

        $callback = fn() => 'result';
        $data = new LifecycleData(
            action: 'create',
            resource: 'User',
            context: new TestLogData(name: 'Test User')
        );

        // Expect logging calls
        $loggable->shouldReceive('info')
            ->once()
            ->with('before.User.create', [$data->context]);

        $loggable->shouldReceive('info')
            ->once()
            ->with('after.User.create', ['result']);

        // Act
        $result = $strategy->executeLogging($data, $callback);

        // Assert
        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_handles_exceptions_properly()
    {
        // Arrange
        $loggable = $this->mockLoggable();
        $strategy = new WithLogging($loggable);

        $exception = new Exception('Test error');
        $data = new LifecycleData(
            action: 'create',
            resource: 'User',
            context: new TestLogData(name: 'Test User')
        );

        // Only expect the before logging - the exception should prevent the after logging
        $loggable->shouldReceive('info')
            ->once()
            ->with('before.User.create', [$data->context]);

        // The callback will throw an exception
        $callback = function () use ($exception) {
            throw $exception;
        };

        // Act & Assert
        try {
            $strategy->executeLogging($data, $callback);
            $this->fail('Exception should have been thrown');
        } catch (Exception $e) {
            $this->assertSame($exception, $e);
        }
    }

    /**
     * Helper to mock Loggable contract
     */
    private function mockLoggable(): MockInterface
    {
        return Mockery::mock(Loggable::class);
    }
}
