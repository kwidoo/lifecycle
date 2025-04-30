<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Contracts\Events\Dispatcher;
use Kwidoo\Lifecycle\Lifecycle\DefaultEventable;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class DefaultEventableTest extends TestCase
{
    #[Test]
    public function test_dispatches_events()
    {
        // Arrange
        $dispatcher = Mockery::mock(Dispatcher::class);
        $eventable = new DefaultEventable($dispatcher);
        $eventName = 'test.event';
        $payload = ['data' => 'test'];

        // Expect
        $dispatcher->shouldReceive('dispatch')
            ->once()
            ->with($eventName, ['data' => $payload]);

        // Act
        $eventable->dispatch($eventName, $payload);
    }
}
