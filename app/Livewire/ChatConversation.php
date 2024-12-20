<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\OpenAIConversationService;
use Livewire\Component;

class ChatConversation extends Component
{
    public Project $project;
    public bool $isStreaming = false;

    /** @var array<array{role: string, content: array|string}> */
    public array $messages = [];
    public string $newMessage = '';
    public string $streamedResponse = '';
    public float $temperature = 0.7;
    public ?string $selectedModel = null;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->messages = $this->project->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => json_decode($message->content, true) ?? $message->content,
                ];
            })
            ->toArray()
        ;

        // Charger les paramètres du projet
        if ($settings = $project->settings) {
            $this->selectedModel = $settings->model ?? 'meta-llama/llama-3.2-11b-vision-instruct';
            $this->temperature = $settings->temperature;
        } else {
            $this->selectedModel = 'meta-llama/llama-3.2-11b-vision-instruct';
        }
    }

    public function sendMessage()
    {
        if (empty($this->newMessage)) {
            return;
        }

        $content = $this->newMessage;

        $this->messages[] = [
            'role' => 'user',
            'content' => $content,
        ];

        $this->project->messages()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        $this->newMessage = '';

        // On lance la réponse après un court délai pour permettre l'affichage du message
        $this->dispatch('scroll-chat');

        $this->dispatch('messageAdded');

        $this->js('$wire.getAiResponse()');
    }

    public function getAiResponse()
    {
        try {
            $this->isStreaming = true;
            $this->dispatch('stream-started');

            logger()->info('Début getAiResponse');
            $openAIService = new OpenAIConversationService();

            $this->streamedResponse = '';

            logger()->info('Messages envoyés:', $this->messages);
            $fullResponse = $openAIService->streamConversation(
                $this->messages,
                $this->selectedModel,
                $this->temperature,
                function ($partial) {
                    logger()->info('Chunk reçu:', ['partial' => $partial]);
                    $this->stream('streamedResponse', $partial);
                }
            );

            // Une fois le streaming terminé, on sauvegarde le message complet
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $fullResponse,
            ];

            $this->project->messages()->create([
                'role' => 'assistant',
                'content' => $fullResponse,
            ]);

            // Générer le titre après le premier échange complet
            if (2 === count($this->messages) || 'Nouvelle conversation' === $this->project->name) {
                try {
                    $title = $openAIService->generateTitle($this->messages);
                    $this->project->update(['name' => $title]);
                } catch (\Exception $e) {
                    logger()->error('Erreur lors de la mise à jour du titre:', [
                        'message' => $e->getMessage(),
                    ]);
                    // On continue même si la génération du titre échoue
                }
            }

            $this->streamedResponse = '';
            $this->isStreaming = false;
            $this->dispatch('stream-ended');
            $this->dispatch('scroll-chat');
        } catch (\Exception $e) {
            $this->isStreaming = false;
            $this->dispatch('stream-ended');
            logger()->error('Erreur dans getAiResponse:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function render()
    {
        $openAIService = new OpenAIConversationService();
        $models = $openAIService->getModels();

        return view('livewire.chat-conversation', [
            'models' => $models,
        ]);
    }

    public function updatedSelectedModel($value)
    {
        if ($this->project->id) {
            $this->project->settings()->updateOrCreate(
                [],
                ['model' => $value]
            );
            $this->dispatch('model-updated');
        }
    }

    public function updatedTemperature($value)
    {
        if ($this->project->id) {
            $this->project->settings()->updateOrCreate(
                [],
                ['temperature' => $value]
            );
            $this->dispatch('temperature-updated');
        }
    }
}
