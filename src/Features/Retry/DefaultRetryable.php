<?php

namespace Kwidoo\Lifecycle\Features\Retry;

use Closure;
use Kwidoo\Lifecycle\Contracts\Features\Retryable;
use Kwidoo\Lifecycle\Data\LifecycleContextData;
use Kwidoo\Lifecycle\Data\LifecycleData;
use Kwidoo\Lifecycle\Data\LifecycleResultData;

class DefaultRetryable implements Retryable
{
    /**
     * Maximum retry attempts
     */
    protected int $maxAttempts;

    /**
     * Delay between retry attempts in milliseconds
     */
    protected int $retryDelay;

    /**
     * @param int $maxAttempts
     * @param int $retryDelay
     */
    public function __construct(int $maxAttempts = 3, int $retryDelay = 100)
    {
        $this->maxAttempts = $maxAttempts;
        $this->retryDelay = $retryDelay;
    }

    /**
     * Retry a callback on failure
     *
     * @param LifecycleContextData|LifecycleData $data
     * @param Closure $callback
     * @return mixed
     * @throws \Throwable
     */
    public function retry(LifecycleContextData|LifecycleData $data, Closure $callback): mixed
    {
        $attempts = 0;
        $lastException = null;
        $resultData = $data instanceof LifecycleContextData ? new LifecycleResultData() : null;

        while ($attempts < $this->maxAttempts) {
            $attempts++;

            try {
                $result = $callback();

                // Track attempts in result data if using the new data structure
                if ($resultData !== null) {
                    $resultData = $resultData->withResult($result)->incrementRetry();
                } elseif ($data instanceof LifecycleData) {
                    // Legacy support - set retry attempts directly
                    $data->retryAttempts = $attempts;
                }

                return $result;
            } catch (\Throwable $e) {
                $lastException = $e;

                if ($attempts >= $this->maxAttempts) {
                    break;
                }

                // Sleep between retries (convert milliseconds to microseconds)
                usleep($this->retryDelay * 1000);
            }
        }

        // Track the failed attempt in result data if using the new data structure
        if ($resultData !== null) {
            $resultData = $resultData->fail()->incrementRetry();
        }

        throw $lastException;
    }
}
