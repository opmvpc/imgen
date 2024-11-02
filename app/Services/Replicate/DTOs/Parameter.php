<?php

namespace App\Services\Replicate\DTOs;

use App\Services\Replicate\Enums\ParameterType;

class Parameter
{
    public function __construct(
        public readonly string $name,
        public readonly ParameterType $type,
        public readonly mixed $default = null,
        public readonly bool $required = false,
        public readonly ?string $description = null,
        public readonly array $validation = [],
        public readonly int $order = 999,
    ) {}
}
