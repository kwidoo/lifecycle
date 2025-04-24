<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Exception;
use Illuminate\Database\DatabaseManager;
use Kwidoo\Lifecycle\Lifecycle\DefaultTransactional;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;

class DefaultTransactionalTest extends TestCase
{
    /** @test */
    public function it_begins_commits_and_returns_result_when_successful()
    {
        // Arrange
        $dbManager = Mockery::mock(DatabaseManager::class);
        $dbManager->shouldReceive('transaction')
            ->once()
            ->withArgs(function ($callback) {
                return is_callable($callback);
            })
            ->andReturn('result');

        $transactional = new DefaultTransactional($dbManager);
        $callback = fn() => 'result';

        // Act
        $result = $transactional->run($callback);

        // Assert
        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_rolls_back_and_throws_exception_when_failed()
    {
        // Arrange
        $exception = new Exception('Transaction failed');
        $dbManager = Mockery::mock(DatabaseManager::class);
        $dbManager->shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        $transactional = new DefaultTransactional($dbManager);
        $callback = fn() => 'result';

        // Act & Assert
        $this->expectExceptionObject($exception);
        $transactional->run($callback);
    }
}
