<?php

namespace Kwidoo\Lifecycle\Factories;

interface RepositoryFactory
{
    /**
     * Create a presenter instance.
     *
     * @param string $presenterClass
     * @param mixed $data
     * @return mixed
     */
    public function make(?string $presenter = null): mixed;
}
