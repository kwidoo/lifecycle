<?php

namespace Kwidoo\Lifecycle\Contracts\Features;

use Spatie\LaravelData\Contracts\BaseData;

interface Authorizer
{
    /**
     * @param string $ability
     * @param BaseData|null $context
     *
     * @return void
     */
    public function authorize(string $ability, ?BaseData $context = null): void;
}
