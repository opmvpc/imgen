<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use OpenAI;

class OpenAIConversationService
{
    private $baseUrl;
    private $apiKey;
    private $client;

    public function __construct()
    {
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->apiKey = config('services.openrouter.api_key');
        $this->client = $this->createOpenAIClient();
    }

    private function createOpenAIClient()
    {
        return OpenAI::factory()
            ->withApiKey($this->apiKey)
            ->withBaseUri($this->baseUrl)
            ->make();
    }

    public function getModels()
    {
        return Cache::remember('openrouter_models', 3600, function () {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/models');

            return collect($response->json()['data'])->pluck('id', 'context_length')->toArray();
        });
    }

    public function streamConversation($messages, $model = null, $temperature = 0.7)
    {
        $models = $this->getModels();
        if (!$model || !isset($models[$model])) {
            $model = array_key_first($models);
        }

        $maxTokens = $models[$model];

        $stream = $this->client->chat()->createStreamed([
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        foreach ($stream as $response) {
            $content = $response->choices[0]->delta->content;
            if ($content !== null) {
                yield $content;
            }
        }
    }
}
