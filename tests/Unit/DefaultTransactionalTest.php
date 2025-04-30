<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Exception;
use Illuminate\Database\DatabaseManager;
use Kwidoo\Lifecycle\Lifecycle\DefaultTransactional;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class DefaultTransactionalTest extends TestCase
{
    #[Test]
    public function test_begins_commits_and_returns_result_when_successful()
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

    #[Test]
    public function test_rolls_back_and_throws_exception_when_failed()
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
