<?php

namespace Kwidoo\Lifecycle\Data;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class LifecycleData extends Data
{
    public function __construct(
        #[Required()]
        public string $action,

        #[Required()]
        public string $resource,

        public mixed $context = null,

        public mixed $result = null,
    ) {}
}
