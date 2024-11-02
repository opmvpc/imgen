<?php

namespace App\Services\Replicate\Models;

use App\Services\Replicate\DTOs\ModelConfig;
use App\Services\Replicate\DTOs\Parameter;
use App\Services\Replicate\Enums\ParameterType;
use App\Services\Replicate\Enums\OutputType;

class RecraftModel extends AbstractReplicateModel
{
    protected function defineConfig(): ModelConfig
    {
        return new ModelConfig(
            name: 'recraft-ai/recraft-v3',
            version: '0jhy3g0nb9rge0cjvvct6dg8jc',
            outputType: OutputType::SINGLE_URI,
            parameters: [
                new Parameter(
                    name: 'prompt',
                    type: ParameterType::STRING,
                    required: true,
                    description: 'Text prompt for image generation',
                    order: 0,
                    validation: [
                        'min' => 3,
                        'max' => 500,
                    ]
                ),
                new Parameter(
                    name: 'size',
                    type: ParameterType::STRING,
                    required: false,
                    default: '1024x1024',
                    description: 'Width and height of the generated image',
                    validation: [
                        'enum' => [
                            '1024x1024',
                            '1365x1024',
                            '1024x1365',
                            '1536x1024',
                            '1024x1536',
                            '1820x1024',
                            '1024x1820',
                            '1024x2048',
                            '2048x1024',
                            '1434x1024',
                            '1024x1434',
                            '1024x1280',
                            '1280x1024',
                            '1024x1707',
                            '1707x1024'
                        ]
                    ],
                    order: 1
                ),
                new Parameter(
                    name: 'style',
                    type: ParameterType::STRING,
                    required: false,
                    default: 'any',
                    description: 'Style of the generated image.',
                    validation: [
                        'enum' => [
                            'any',
                            'realistic_image',
                            'digital_illustration',
                            'digital_illustration/pixel_art',
                            'digital_illustration/hand_drawn',
                            'digital_illustration/grain',
                            'digital_illustration/infantile_sketch',
                            'digital_illustration/2d_art_poster',
                            'digital_illustration/handmade_3d',
                            'digital_illustration/hand_drawn_outline',
                            'digital_illustration/engraving_color',
                            'digital_illustration/2d_art_poster_2',
                            'realistic_image/b_and_w',
                            'realistic_image/hard_flash',
                            'realistic_image/hdr',
                            'realistic_image/natural_light',
                            'realistic_image/studio_portrait',
                            'realistic_image/enterprise',
                            'realistic_image/motion_blur'
                        ]
                    ],
                    order: 2
                ),
            ]
        );
    }
}
