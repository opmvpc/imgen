<?php

namespace App\Services\Replicate\Models;

use App\Services\Replicate\Contracts\ReplicateModelInterface;
use App\Services\Replicate\DTOs\ModelConfig;
use App\Services\Replicate\DTOs\Parameter;
use App\Services\Replicate\Enums\ParameterType;

class FluxModel implements ReplicateModelInterface
{
    private ModelConfig $config;

    public function __construct()
    {
        $this->config = new ModelConfig(
            name: 'black-forest-labs/flux-1.1-pro',
            version: '8beff3369e81422112d93b89ca01426147de542cd4684c244b673b105188fe5f',
            parameters: [
                new Parameter(
                    name: 'prompt',
                    type: ParameterType::STRING,
                    required: true,
                    description: 'Text prompt for image generation',
                    order: 1,
                    validation: [
                        'min' => 3
                    ]
                ),
                new Parameter(
                    name: 'aspect_ratio',
                    type: ParameterType::STRING,
                    required: false,
                    default: '1:1',
                    description: 'Aspect ratio for the generated image',
                    validation: [
                        'enum' => [
                            '1:1',
                            '16:9',
                            '2:3',
                            '3:2',
                            '4:5',
                            '5:4',
                            '9:16',
                            '3:4',
                            '4:3',
                            'custom'
                        ]
                    ],
                    order: 2
                ),
                new Parameter(
                    name: 'width',
                    type: ParameterType::INTEGER,
                    required: false,
                    description: 'Width of the generated image in text-to-image mode. Only used when aspect_ratio=custom. Must be a multiple of 32 (if it\'s not, it will be rounded to nearest multiple of 32). Note: Ignored in img2img and inpainting modes.',
                    validation: [
                        'min' => 256,
                        'max' => 1440
                    ],
                    order: 3
                ),
                new Parameter(
                    name: 'height',
                    type: ParameterType::INTEGER,
                    required: false,
                    description: 'Height of the generated image in text-to-image mode. Only used when aspect_ratio=custom. Must be a multiple of 32 (if it\'s not, it will be rounded to nearest multiple of 32). Note: Ignored in img2img and inpainting modes.',
                    validation: [
                        'min' => 256,
                        'max' => 1440
                    ],
                    order: 4
                ),
                new Parameter(
                    name: 'output_format',
                    type: ParameterType::STRING,
                    required: false,
                    default: 'webp',
                    description: 'Format of the output images.',
                    validation: [
                        'enum' => ['webp', 'jpg', 'png']
                    ],
                    order: 5
                ),
                new Parameter(
                    name: 'output_quality',
                    type: ParameterType::INTEGER,
                    required: false,
                    default: 100,
                    description: 'Quality when saving the output images, from 0 to 100. 100 is best quality, 0 is lowest quality. Not relevant for .png outputs',
                    validation: [
                        'min' => 0,
                        'max' => 100
                    ],
                    order: 6
                ),
                new Parameter(
                    name: 'safety_tolerance',
                    type: ParameterType::INTEGER,
                    required: false,
                    default: 5,
                    description: 'Safety tolerance, 1 is most strict and 5 is most permissive',
                    validation: [
                        'min' => 1,
                        'max' => 5
                    ],
                    order: 7
                ),
                new Parameter(
                    name: 'seed',
                    type: ParameterType::INTEGER,
                    required: false,
                    description: 'Random seed. Set for reproducible generation',
                    validation: [
                        'min' => 0,
                        'max' => PHP_INT_MAX
                    ],
                    order: 8
                ),
                new Parameter(
                    name: 'prompt_upsampling',
                    type: ParameterType::BOOLEAN,
                    required: false,
                    default: false,
                    description: 'Automatically modify the prompt for more creative generation',
                    order: 9
                ),
            ]
        );
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
        // TODO: Implémenter la validation
        return true;
    }

    public function getValidationRules(): array
    {
        return $this->config->getValidationRules();
    }
}
