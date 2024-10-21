<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\OpenAIConversationService;
use App\Models\Project;

class ChatConversation extends Component
{
    public $project;
    public $messages = [];
    public $newMessage = '';
    public $streamedResponse = '';
    public $temperature = 0.7;
    public $selectedModel = null;
    public $imageUrl = '';

    public function mount($project = null)
    {
        $this->project = $project;
    }

    public function sendMessage()
    {
        if (!$this->project) {
            $this->createProject();
        }

        $content = $this->newMessage;
        if ($this->imageUrl) {
            $content = [
                ['type' => 'text', 'text' => $this->newMessage],
                ['type' => 'image_url', 'image_url' => ['url' => $this->imageUrl]],
            ];
        }

        $this->messages[] = ['role' => 'user', 'content' => $content];
        $this->newMessage = '';
        $this->imageUrl = '';

        $this->streamResponse();
    }

    private function createProject()
    {
        $this->project = auth()->user()->projects()->create([
            'name' => 'Nouvelle conversation ' . now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function streamResponse()
    {
        $openAIService = new OpenAIConversationService();
        $stream = $openAIService->streamConversation($this->messages, $this->selectedModel, $this->temperature);

        foreach ($stream as $chunk) {
            $this->streamedResponse .= $chunk;
            $this->dispatch('updateStreamedResponse', $this->streamedResponse);
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $this->streamedResponse];
        $this->streamedResponse = '';

        // Sauvegarder les messages dans le projet
        $this->project->messages()->createMany($this->messages);
    }

    public function render()
    {
        $openAIService = new OpenAIConversationService();
        $models = $openAIService->getModels();

        return view('livewire.chat-conversation', [
            'models' => $models,
        ]);
    }
}
