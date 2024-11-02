<?php

namespace App\Services\Replicate\Contracts;

interface ReplicateModelInterface
{
    public function getName(): string;
    public function getVersion(): string;
    public function getParameters(): array;
    public function validateParameters(array $input): bool;
    public function getValidationRules(): array;
}
