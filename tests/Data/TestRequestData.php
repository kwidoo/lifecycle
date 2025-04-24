<?php

namespace Kwidoo\Lifecycle\Tests\Data;

use Spatie\LaravelData\Data;

class TestRequestData extends Data
{
    public function __construct(
        public int $id = 1,
        public string $name = 'Test'
    ) {
    }
}
