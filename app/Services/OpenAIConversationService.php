<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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

    public function getModels()
    {
        return cache()->remember('openai.models', now()->addHour(), function () {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
            ])->get($this->baseUrl.'/models');

            return collect($response->json()['data'])
                ->filter(function ($model) {
                    return isset($model['architecture']['modality'])
                        && 'text+image->text' === $model['architecture']['modality'];
                })
                ->sortBy('name')
                ->map(function ($model) {
                    return [
                        'id' => $model['id'],
                        'name' => $model['name'],
                        'context_length' => $model['context_length'],
                        'max_completion_tokens' => $model['top_provider']['max_completion_tokens'],
                        'pricing' => $model['pricing'],
                    ];
                })
                ->values()
                ->all()
            ;
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
            'max_tokens' => 4096,
        ]);

        foreach ($stream as $response) {
            $content = $response->choices[0]->delta->content;
            if (null !== $content) {
                yield $content;
            }
        }
    }

    private function createOpenAIClient()
    {
        return \OpenAI::factory()
            ->withApiKey($this->apiKey)
            ->withBaseUri($this->baseUrl)
            ->make()
        ;
    }
}
