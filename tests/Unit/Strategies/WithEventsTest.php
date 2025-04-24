<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Strategies;

use Illuminate\Support\Facades\Event;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Eventable;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Strategies\WithEvents;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Spatie\LaravelData\Data;

class TestEventData extends Data
{
    public function __construct(
        public string $name = 'test'
    ) {
    }
}

class WithEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_dispatches_before_and_after_events()
    {
        // Arrange
        $eventable = $this->mockEventable();
        $strategy = new WithEvents($eventable);

        $callback = fn() => 'result';
        $data = new LifecycleData(
            action: 'create',
            resource: 'User',
            context: new TestEventData(name: 'Test User')
        );

        // Expect events to be dispatched
        $eventable->shouldReceive('dispatch')
            ->once()
            ->with('before.User.create', $data->context);

        $eventable->shouldReceive('dispatch')
            ->once()
            ->with('after.User.create', $data->context);

        // Act
        $result = $strategy->executeEvents($data, $callback);

        // Assert
        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_handles_exceptions_properly()
    {
        // Arrange
        $eventable = $this->mockEventable();
        $strategy = new WithEvents($eventable);

        $exception = new \Exception('Test error');
        $data = new LifecycleData(
            action: 'create',
            resource: 'User',
            context: new TestEventData(name: 'Test User')
        );

        // Only expect the before event - the exception should prevent the after event
        $eventable->shouldReceive('dispatch')
            ->once()
            ->with('before.User.create', $data->context);

        // The callback will throw an exception
        $callback = function () use ($exception) {
            throw $exception;
        };

        // Act & Assert
        try {
            $strategy->executeEvents($data, $callback);
            $this->fail('Exception should have been thrown');
        } catch (\Exception $e) {
            $this->assertSame($exception, $e);
        }
    }

    /**
     * Helper to mock Eventable contract
     */
    private function mockEventable(): MockInterface
    {
        return Mockery::mock(Eventable::class);
    }
}
