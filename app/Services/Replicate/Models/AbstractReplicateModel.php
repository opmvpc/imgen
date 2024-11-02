<?php

namespace App\Services\Replicate\Models;

use App\Services\Replicate\Contracts\ReplicateModelInterface;
use App\Services\Replicate\DTOs\ModelConfig;

abstract class AbstractReplicateModel implements ReplicateModelInterface
{
    protected ModelConfig $config;

    public function __construct()
    {
        $this->config = $this->defineConfig();
    }

    public function getName(): string
    {
        return $this->config->name;
    }

    public function getVersion(): string
    {
        return $this->config->version;
    }

    public function getParameters(): array
    {
        $parameters = $this->config->parameters;
        usort($parameters, fn($a, $b) => $a->order <=> $b->order);
        return $parameters;
    }

    public function validateParameters(array $input): bool
    {
        // TODO: Implémenter la validation commune si nécessaire
        return true;
    }

    public function getValidationRules(): array
    {
        return $this->config->getValidationRules();
    }

    public function getOutputUrl(array $result): ?string
    {
        return $this->config->getOutputUrl($result);
    }

    abstract protected function defineConfig(): ModelConfig;
}
