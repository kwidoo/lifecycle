<?php

namespace Kwidoo\Lifecycle\Tests\Data;

use Spatie\LaravelData\Data;

class TestData extends Data
{
    public function __construct(
        public string $name = 'test'
    ) {
    }
}
