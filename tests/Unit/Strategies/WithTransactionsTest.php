<?php

namespace Kwidoo\Lifecycle\Tests\Unit\Strategies;

use Exception;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Transactional;
use Kwidoo\Lifecycle\Strategies\WithTransactions;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

class WithTransactionsTest extends TestCase
{
    /** @test */
    public function it_wraps_operation_in_transaction()
    {
        // Arrange
        $transactional = $this->mockTransactional();
        $strategy = new WithTransactions($transactional);

        $callback = fn() => 'result';

        // Setup transaction mock behavior
        $transactional->shouldReceive('run')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function($cb) {
                return $cb();
            });

        // Act
        $result = $strategy->executeTransactions($callback);

        // Assert
        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_passes_exception_from_transaction()
    {
        // Arrange
        $transactional = $this->mockTransactional();
        $strategy = new WithTransactions($transactional);

        $exception = new Exception('Transaction failed');
        $callback = fn() => 'result';

        // Setup transaction mock to throw exception
        $transactional->shouldReceive('run')
            ->once()
            ->andThrow($exception);

        // Act & Assert
        $this->expectExceptionObject($exception);
        $strategy->executeTransactions($callback);
    }

    /** @test */
    public function it_uses_default_connection_when_not_specified()
    {
        // Arrange
        $transactional = $this->mockTransactional();
        $strategy = new WithTransactions($transactional);

        $callback = fn() => 'result';

        // Expect transaction call with default parameters
        $transactional->shouldReceive('run')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnUsing(function($cb) {
                return $cb();
            });

        // Act
        $result = $strategy->executeTransactions($callback);

        // Assert
        $this->assertEquals('result', $result);
    }

    /**
     * Helper to mock Transactional contract
     */
    private function mockTransactional(): MockInterface
    {
        return Mockery::mock(Transactional::class);
    }
}
