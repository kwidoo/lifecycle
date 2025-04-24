<?php

namespace Kwidoo\Lifecycle\Lifecycle;

use Illuminate\Database\DatabaseManager;
use Kwidoo\Lifecycle\Contracts\Lifecycle\Transactional;

class DefaultTransactional implements Transactional
{
    public function __construct(protected DatabaseManager $db)
    {
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function run(callable $callback)
    {
        return $this->db->transaction($callback);
    }
}
