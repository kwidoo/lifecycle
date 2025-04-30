<?php

namespace Kwidoo\Lifecycle\Tests\Unit;

use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Kwidoo\Lifecycle\Lifecycle\DefaultLoggable;
use Kwidoo\Lifecycle\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class DefaultLoggableTest extends TestCase
{
    #[Test]
    public function test_logs_info_messages()
    {
        // Arrange
        $logger = Mockery::mock(LoggerInterface::class);
        $logManager = Mockery::mock(LogManager::class);
        $logManager->shouldReceive('channel')
            ->once()
            ->with('daily')
            ->andReturn($logger);

        $logger->shouldReceive('info')
            ->once()
            ->with('Test message', ['context' => 'data']);

        $loggable = new DefaultLoggable($logManager);

        // Act
        $loggable->info('Test message', ['context' => 'data']);
    }

    #[Test]
    public function test_logs_error_messages()
    {
        // Arrange
        $logger = Mockery::mock(LoggerInterface::class);
        $logManager = Mockery::mock(LogManager::class);
        $logManager->shouldReceive('channel')
            ->once()
            ->with('daily')
            ->andReturn($logger);

        $logger->shouldReceive('error')
            ->once()
            ->with('Error message', ['error' => 'data']);

        $loggable = new DefaultLoggable($logManager);

        // Act
        $loggable->error('Error message', ['error' => 'data']);
    }

    #[Test]
    public function test_logs_debug_messages()
    {
        // Arrange
        $logger = Mockery::mock(LoggerInterface::class);
        $logManager = Mockery::mock(LogManager::class);
        $logManager->shouldReceive('channel')
            ->once()
            ->with('daily')
            ->andReturn($logger);

        $logger->shouldReceive('debug')
            ->once()
            ->with('Debug message', ['debug' => 'data']);

        $loggable = new DefaultLoggable($logManager);

        // Act
        $loggable->debug('Debug message', ['debug' => 'data']);
    }
}
