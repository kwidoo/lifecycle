<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Strategies\NoopErrorStrategy;
use Kwidoo\Lifecycle\Tests\TestCase;

class NoopErrorStrategyTest extends TestCase
{
    private NoopErrorStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new NoopErrorStrategy();
    }

    public function testHandleErrorRethrowsTheException()
    {
        $data = new LifecycleContextData('resource', 'action', ['test' => 'context']);
        $exception = new \RuntimeException('Test exception');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Test exception');

        $this->strategy->handleError($data, $exception);
    }
}
