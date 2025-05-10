<?php

namespace Kwidoo\Lifecycle\Data;

use Spatie\LaravelData\Data;

class LifecycleResultData extends Data
{
    /**
     * @param mixed $result
     * @param int $retryAttempts
     * @param string|null $status
     * @param float|null $completedAt
     * @param float|null $executionTime
     */
    public function __construct(
        public readonly mixed $result = null,
        public readonly int $retryAttempts = 0,
        public readonly ?string $status = null,
        public readonly ?float $completedAt = null,
        public readonly ?float $executionTime = null,
    ) {}

    /**
     * Create a new instance with updated properties
     *
     * @param array $parameters
     * @return self
     */
    public function copy(...$parameters): self
    {
        return new self(
            result: $parameters['result'] ?? $this->result,
            retryAttempts: $parameters['retryAttempts'] ?? $this->retryAttempts,
            status: $parameters['status'] ?? $this->status,
            completedAt: $parameters['completedAt'] ?? $this->completedAt,
            executionTime: $parameters['executionTime'] ?? $this->executionTime,
        );
    }

    /**
     * Create a new instance with the given result
     *
     * @param mixed $result
     * @return self
     */
    public function withResult(mixed $result): self
    {
        return $this->copy(result: $result);
    }

    /**
     * Create a new instance with an incremented retry attempt count
     *
     * @return self
     */
    public function incrementRetry(): self
    {
        return $this->copy(retryAttempts: $this->retryAttempts + 1);
    }

    /**
     * Create a new instance with the completed status and timestamp
     *
     * @param float|null $startedAt
     * @return self
     */
    public function complete(?float $startedAt = null): self
    {
        $completedAt = microtime(true);
        $executionTime = $startedAt ? ($completedAt - $startedAt) : null;

        return $this->copy(
            status: 'completed',
            completedAt: $completedAt,
            executionTime: $executionTime
        );
    }

    /**
     * Create a new instance with the failed status
     *
     * @return self
     */
    public function fail(): self
    {
        return $this->copy(status: 'failed');
    }

    /**
     * Convert to array for debug or logging purposes
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'result' => $this->result,
            'retryAttempts' => $this->retryAttempts,
            'status' => $this->status,
            'completedAt' => $this->completedAt,
            'executionTime' => $this->executionTime,
        ];
    }
}
