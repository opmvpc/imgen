<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIConversationService
{
    private $baseUrl;
    private $apiKey;
    private $client;
    private const DEFAULT_MODEL = 'meta-llama/llama-3.2-11b-vision-instruct';

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

    public function streamConversation($messages, $model = null, $temperature = 0.7, ?callable $callback = null): string
    {
        try {
            logger()->info('Début streamConversation', [
                'model' => $model,
                'temperature' => $temperature,
            ]);

            $models = $this->getModels();
            if (!$model || !isset($models[$model])) {
                $model = self::DEFAULT_MODEL;
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
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function generateTitle(array $messages): string
    {
        try {
            logger()->info('Génération du titre de la conversation');

            $systemPrompt = <<<'EOT'
Tu es un expert en création de titres concis et pertinents. Ta tâche est d'analyser la conversation qui suit et d'en extraire un titre significatif.

RÈGLES :
- Le titre doit faire entre 2 et 5 mots
- Pas de ponctuation ni de guillemets
- Capturer l'essence ou le sujet principal de la conversation
- Être facilement compréhensible
- Éviter les mots génériques comme "Discussion sur" ou "Conversation à propos de"
- Préférer des noms et adjectifs spécifiques

EXEMPLES :
Conversation: "Comment optimiser les performances de mon site Laravel?"
Titre: Optimisation Performance Laravel

Conversation: "Peux-tu m'expliquer la théorie de la relativité d'Einstein?"
Titre: Théorie Relativité Einstein

Conversation: "J'aimerais des conseils pour améliorer mon CV et ma lettre de motivation"
Titre: Conseils CV Professionnel

IMPORTANT: Réponds UNIQUEMENT avec le titre, sans autre texte ni explications.
EOT;

            $promptMessages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                ...$messages,
            ];

            $response = $this->client->chat()->create([
                'model' => 'openai/gpt-4o-mini',
                'messages' => $promptMessages,
                'temperature' => 0.7,
                'max_tokens' => 10,
            ]);

            $title = trim($response->choices[0]->message->content);
            logger()->info('Titre généré:', ['title' => $title]);

            return $title;
        } catch (\Exception $e) {
            logger()->error('Erreur lors de la génération du titre:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
