<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Illuminate\Log\LogManager;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Loggable;
use Psr\Log\LoggerInterface;

class DefaultLoggable implements Loggable
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;


    public function __construct(LogManager $logManager, string $channel = 'daily')
    {
        $this->logger = $logManager->channel($channel);
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }
}
