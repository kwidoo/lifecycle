<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Strategies\NoopTransactionStrategy;
use Kwidoo\Lifecycle\Tests\TestCase;

class NoopTransactionStrategyTest extends TestCase
{
    private NoopTransactionStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new NoopTransactionStrategy();
    }

    public function testExecuteSimplyCallsTheCallback()
    {
        $callbackCalled = false;
        $expectedResult = 'test-result';

        $callback = function () use (&$callbackCalled, $expectedResult) {
            $callbackCalled = true;
            return $expectedResult;
        };

        $result = $this->strategy->execute($callback);

        $this->assertTrue($callbackCalled, 'Callback should be executed');
        $this->assertEquals($expectedResult, $result, 'Result should be returned from callback');
    }
}
