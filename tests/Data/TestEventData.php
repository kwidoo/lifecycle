<?php

namespace Kwidoo\Lifecycle\Tests\Data;

use Spatie\LaravelData\Data;

class TestEventData extends Data
{
    public function __construct(
        public string $name = 'test'
    ) {
    }
}
