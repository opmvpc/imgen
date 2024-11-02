<?php

namespace App\Services\Replicate\Enums;

enum OutputType
{
    case SINGLE_URI;    // Pour Flux et Recraft
    case ARRAY_OF_URI;  // Pour Stable Diffusion
} 
