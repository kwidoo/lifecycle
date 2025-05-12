<?php

namespace Kwidoo\Lifecycle\CQRS\Contracts;

/**
 * Interface for Read Model Repositories
 *
 * Read Model Repositories provide access to read models (projections)
 * that are optimized for querying.
 */
interface ReadModelRepository
{
    /**
     * Find a model by its ID
     *
     * @param string|int $id
     * @return mixed The read model or null if not found
     */
    public function findById(string|int $id): mixed;

    /**
     * Find models matching the given criteria
     *
     * @param array $criteria
     * @param array $orderBy Optional ordering
     * @param int|null $limit Optional limit
     * @param int $offset Optional offset
     * @return array Collection of read models
     */
    public function findByCriteria(
        array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        int $offset = 0
    ): array;
}
