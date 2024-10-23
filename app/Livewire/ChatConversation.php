<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\OpenAIConversationService;
use Livewire\Component;

class ChatConversation extends Component
{
    public $project;
    public $messages = [];
    public $newMessage = '';
    public $streamedResponse = '';
    public $temperature = 0.7;
    public $selectedModel;
    public $imageUrl = '';

    public function mount(?Project $project)
    {
        if ($project) {
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
        }
    }

    public function sendMessage()
    {
        if (empty($this->newMessage)) {
            return;
        }

        $content = $this->newMessage;
        if ($this->imageUrl) {
            $content = [
                ['type' => 'text', 'text' => $this->newMessage],
                ['type' => 'image_url', 'image_url' => ['url' => $this->imageUrl]],
            ];
        }

        $isNewProject = null === $this->project->id;
        if ($isNewProject) {
            $this->createProject();
        }

        $message = $this->project->messages()->create([
            'role' => 'user',
            'content' => json_encode($content),
        ]);

        $this->messages[] = [
            'role' => 'user',
            'content' => $content,
        ];

        $this->newMessage = '';
        $this->imageUrl = '';

        if ($isNewProject) {
            redirect()->route('chat', $this->project);
        }
        $this->streamResponse();
    }

    public function streamResponse()
    {
        $openAIService = new OpenAIConversationService();
        $stream = $openAIService->streamConversation($this->messages, $this->selectedModel, $this->temperature);

        foreach ($stream as $chunk) {
            $this->streamedResponse .= $chunk;
            $this->dispatch('updateStreamedResponse', $this->streamedResponse);
        }

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $this->streamedResponse,
        ];

        $this->project->messages()->create([
            'role' => 'assistant',
            'content' => $this->streamedResponse,
        ]);

        $this->streamedResponse = '';
    }

    public function render()
    {
        $openAIService = new OpenAIConversationService();
        $models = $openAIService->getModels();

        return view('livewire.chat-conversation', [
            'models' => $models,
        ]);
    }

    private function createProject()
    {
        $this->project = auth()->user()->projects()->create([
            'name' => 'Nouvelle conversation '.now()->format('Y-m-d H:i:s'),
        ]);
    }
}
