<?php

namespace Kwidoo\Lifecycle\Tests\Data;

use Spatie\LaravelData\Data;

class TestEntityData extends Data
{
    public function __construct(
        public int $id = 1,
        public string $name = 'Test Entity'
    ) {
    }
}
