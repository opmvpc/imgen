<?php

namespace App\Services\Replicate\DTOs;

use App\Services\Replicate\Enums\ParameterType;
use App\Services\Replicate\Enums\OutputType;

class ModelConfig
{
    /**
     * @param Parameter[] $parameters
     */
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly array $parameters,
        public readonly OutputType $outputType,
        public readonly ?string $description = null,
    ) {}

    /**
     * Get Laravel validation rules for this model's parameters
     *
     * @return array<string, array<string>>
     */
    public function getValidationRules(): array
    {
        $rules = [];

        /** @var Parameter $parameter */
        foreach ($this->parameters as $parameter) {
            $paramRules = [];

            // Gestion required/nullable
            $paramRules[] = $parameter->required ? 'required' : 'nullable';

            // Gestion du type
            $paramRules[] = match($parameter->type) {
                ParameterType::STRING => 'string',
                ParameterType::INTEGER => 'integer',
                ParameterType::FLOAT => 'numeric',
                ParameterType::BOOLEAN => 'boolean',
                ParameterType::ARRAY => 'array',
                ParameterType::OBJECT => 'array',
            };

            // Gestion des validations spécifiques
            foreach ($parameter->validation as $rule => $value) {
                match($rule) {
                    'min' => $paramRules[] = "min:$value",
                    'max' => $paramRules[] = "max:$value",
                    'enum' => $paramRules[] = 'in:' . implode(',', $value),
                    default => null,
                };
            }

            $rules[$parameter->name] = $paramRules;
        }

        return $rules;
    }

    public function getOutputUrl(array $result): ?string
    {
        return match($this->outputType) {
            OutputType::SINGLE_URI => $result['output'] ?? null,
            OutputType::ARRAY_OF_URI => $result['output'][0] ?? null,
        };
    }
}
