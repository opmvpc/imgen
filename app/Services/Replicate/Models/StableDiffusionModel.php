<?php

namespace App\Services\Replicate\Models;

use App\Services\Replicate\DTOs\ModelConfig;
use App\Services\Replicate\DTOs\Parameter;
use App\Services\Replicate\Enums\ParameterType;
use App\Services\Replicate\Enums\OutputType;

class StableDiffusionModel extends AbstractReplicateModel
{
protected function defineConfig(): ModelConfig
    {
        return new ModelConfig(
            name: 'stability-ai/stable-diffusion-3.5-large',
            version: '0jhy3g0nb9rge0cjvvct6dg8jc',  // À remplacer par la version correcte
            outputType: OutputType::ARRAY_OF_URI,
            parameters: [
                new Parameter(
                    name: 'prompt',
                    type: ParameterType::STRING,
                    required: true,
                    description: 'Text prompt for image generation',
                    order: 0,
                ),
                new Parameter(
                    name: 'aspect_ratio',
                    type: ParameterType::STRING,
                    required: false,
                    default: '1:1',
                    description: 'The aspect ratio of your output image. This value is ignored if you are using an input image.',
                    validation: [
                        'enum' => [
                            '1:1', '16:9', '21:9', '3:2', '2:3',
                            '4:5', '5:4', '3:4', '4:3', '9:16', '9:21'
                        ]
                    ],
                    order: 1
                ),
                new Parameter(
                    name: 'cfg',
                    type: ParameterType::FLOAT,
                    required: false,
                    default: 4.5,
                    description: 'The guidance scale tells the model how similar the output should be to the prompt.',
                    validation: [
                        'min' => 0,
                        'max' => 20
                    ],
                    order: 2
                ),
                new Parameter(
                    name: 'image',
                    type: ParameterType::STRING,
                    required: false,
                    description: 'Input image for image to image mode. The aspect ratio of your output will match this image.',
                    order: 3
                ),
                new Parameter(
                    name: 'prompt_strength',
                    type: ParameterType::FLOAT,
                    required: false,
                    default: 0.85,
                    description: 'Prompt strength (or denoising strength) when using image to image. 1.0 corresponds to full destruction of information in image.',
                    order: 4
                ),
                new Parameter(
                    name: 'steps',
                    type: ParameterType::INTEGER,
                    required: false,
                    default: 40,
                    description: 'Number of steps to run the sampler for.',
                    validation: [
                        'min' => 1,
                        'max' => 50
                    ],
                    order: 5
                ),
                new Parameter(
                    name: 'seed',
                    type: ParameterType::INTEGER,
                    required: false,
                    description: 'Set a seed for reproducibility. Random by default.',
                    order: 6
                ),
                new Parameter(
                    name: 'output_format',
                    type: ParameterType::STRING,
                    required: false,
                    default: 'webp',
                    description: 'Format of the output images',
                    validation: [
                        'enum' => ['webp', 'jpg', 'png']
                    ],
                    order: 7
                ),
                new Parameter(
                    name: 'output_quality',
                    type: ParameterType::INTEGER,
                    required: false,
                    default: 100,
                    description: 'Quality of the output images, from 0 to 100. 100 is best quality, 0 is lowest quality.',
                    validation: [
                        'min' => 0,
                        'max' => 100
                    ],
                    order: 8
                ),
            ]
        );
    }
}
