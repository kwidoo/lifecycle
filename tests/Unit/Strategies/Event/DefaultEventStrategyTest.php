<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Strategies\Event;

use Kwidoo\Lifecycle\Contracts\Features\Eventable;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Features\Event\EventKeyBuilder;
use Kwidoo\Lifecycle\Strategies\Event\DefaultEventStrategy;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class DefaultEventStrategyTest extends TestCase
{
    private Eventable|MockInterface $eventable;
    private EventKeyBuilder|MockInterface $keyBuilder;
    private DefaultEventStrategy $strategy;
    private LifecycleData $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventable = Mockery::mock(Eventable::class);
        $this->keyBuilder = Mockery::mock(EventKeyBuilder::class);
        $this->strategy = new DefaultEventStrategy($this->eventable, $this->keyBuilder);

        $this->data = new LifecycleData(
            action: 'update',
            resource: 'user',
            context: ['id' => 123],
            result: null
        );
    }

    /** @test */
    public function it_dispatches_before_and_after_events_during_execution()
    {
        // Setup expectations
        $this->keyBuilder->shouldReceive('buildBeforeKey')
            ->once()
            ->with('update', 'user')
            ->andReturn('before.user.update');

        $this->keyBuilder->shouldReceive('buildAfterKey')
            ->once()
            ->with('update', 'user')
            ->andReturn('after.user.update');

        $this->eventable->shouldReceive('dispatch')
            ->once()
            ->with('before.user.update', $this->data->context);

        $this->eventable->shouldReceive('dispatch')
            ->once()
            ->with('after.user.update', $this->data->context);

        // Define test callback
        $callbackExecuted = false;
        $callback = function () use (&$callbackExecuted) {
            $callbackExecuted = true;
            return 'result';
        };

        // Execute
        $result = $this->strategy->execute($this->data, $callback);

        // Assert
        $this->assertTrue($callbackExecuted, 'Callback should be executed');
        $this->assertEquals('result', $result, 'Should return the callback result');
        $this->assertEquals('result', $this->data->result, 'Should set the result on data object');
    }

    /** @test */
    public function it_dispatches_error_events()
    {
        // Setup expectations
        $this->keyBuilder->shouldReceive('buildErrorKey')
            ->once()
            ->with('update', 'user')
            ->andReturn('error.user.update');

        $this->eventable->shouldReceive('dispatch')
            ->once()
            ->with('error.user.update', $this->data->result);

        // Execute
        $this->strategy->dispatchError($this->data);

        // Mockery will verify the expectations
    }
}
