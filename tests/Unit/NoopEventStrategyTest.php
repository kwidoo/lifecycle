<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Closure;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Strategies\NoopEventStrategy;
use Kwidoo\Lifecycle\Tests\TestCase;

class NoopEventStrategyTest extends TestCase
{
    private NoopEventStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new NoopEventStrategy();
    }

    public function testExecuteSimplyCallsTheCallback()
    {
        $callbackCalled = false;
        $expectedResult = 'test-result';

        $data = new LifecycleContextData('resource', 'action', ['test' => 'context']);

        $callback = function () use (&$callbackCalled, $expectedResult) {
            $callbackCalled = true;
            return $expectedResult;
        };

        $result = $this->strategy->execute($data, $callback);

        $this->assertTrue($callbackCalled, 'Callback should be executed');
        $this->assertEquals($expectedResult, $result, 'Result should be returned from callback');
    }

    public function testDispatchErrorDoesNothing()
    {
        $data = new LifecycleContextData('resource', 'action', ['test' => 'context']);

        // This should not throw any exceptions
        $this->strategy->dispatchError($data);

        // No assertions needed as we're just confirming it doesn't error
        $this->addToAssertionCount(1);
    }
}
