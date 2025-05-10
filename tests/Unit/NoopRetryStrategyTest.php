<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Strategies\NoopRetryStrategy;
use Kwidoo\Lifecycle\Tests\TestCase;

class NoopRetryStrategyTest extends TestCase
{
    private NoopRetryStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new NoopRetryStrategy();
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

    public function testCallbackExceptionIsPropagated()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        $data = new LifecycleContextData('resource', 'action', ['test' => 'context']);

        $callback = function () {
            throw new \RuntimeException('Test exception');
        };

        $this->strategy->execute($data, $callback);
    }
}
