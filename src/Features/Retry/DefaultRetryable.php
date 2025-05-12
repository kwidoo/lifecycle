<?php

namespace Kwidoo\Lifecycle\Features\Retry;

use Kwidoo\Lifecycle\Contracts\Features\Retryable;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;
use Throwable;

class DefaultRetryable implements Retryable
{

    /**
     * @param int $maxAttempts
     * @param int $retryDelay
     */
    public function __construct(protected int $maxAttempts = 3, protected int $retryDelay = 100) {}

    /**
     * Retry a callback on failure
     *
     * @param LifecycleContextData $data
     * @param callable $callback
     * @return mixed
     * @throws Throwable
     */
    public function retry(LifecycleContextData $data, callable $callback): LifecycleResultData
    {
        $attempts = 0;
        $lastException = null;
        $contextData = $data->withStartTime();
        $resultData = new LifecycleResultData();

        while ($attempts < $this->maxAttempts) {
            $attempts++;

            try {
                $result = $callback();

                return $resultData
                    ->withResult($result)
                    ->incrementRetry()
                    ->complete($contextData->startedAt);
            } catch (Throwable $e) {
                $lastException = $e;

                if ($attempts >= $this->maxAttempts) {
                    break;
                }

                usleep($this->retryDelay * 1000); // convert ms to µs
            }
        }

        throw $lastException;
    }
}
