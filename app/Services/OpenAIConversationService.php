<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use OpenAI\Responses\StreamResponse;
use Illuminate\Support\Facades\Log;

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

    public function streamConversation($messages, $model = null, $temperature = 0.7, callable $callback = null): string
    {
        try {
            logger()->info('Début streamConversation', [
                'model' => $model,
                'temperature' => $temperature
            ]);

            $models = $this->getModels();
            if (!$model || !isset($models[$model])) {
                $model = array_key_first($models);
                logger()->info('Modèle par défaut utilisé:', ['model' => $model]);
            }

            $stream = $this->client->chat()->createStreamed([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => 4096,
            ]);

            logger()->info('Stream créé');

            $fullResponse = '';
            foreach ($stream as $response) {
                $text = $response->choices[0]->delta->content;
                if ($text) {
                    logger()->info('Chunk reçu dans le service:', ['text' => $text]);
                    if ($callback) {
                        $callback($text);
                    }
                    $fullResponse .= $text;
                }
            }

            logger()->info('Stream terminé', ['fullResponse' => $fullResponse]);
            return $fullResponse;
        } catch (\Exception $e) {
            logger()->error('Erreur dans streamConversation:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
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
