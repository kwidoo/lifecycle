<?php

namespace Kwidoo\Lifecycle\Contracts\Lifecycle;

interface Transactional
{
    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function run(callable $callback);
}
