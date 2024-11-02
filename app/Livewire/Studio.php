<?php

namespace App\Livewire;

use App\Models\Generation;
use App\Services\ReplicateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Studio extends Component
{
    public string $prompt = '';
    public bool $isGenerating = false;
    public string $selectedModel = 'black-forest-labs/flux-1.1-pro';
    public array $parameters = [];
    public ?Generation $currentGeneration = null;

    protected $listeners = [
        'generation-completed' => 'handleGenerationCompleted',
        'generation-failed' => 'handleGenerationFailed'
    ];

    public function mount()
    {
        $this->initializeModelParameters();
    }

    private function initializeModelParameters()
    {
        $replicate = new ReplicateService();
        $model = $replicate->getModel($this->selectedModel);

        if ($model) {
            foreach ($model->getParameters() as $parameter) {
                if ($parameter->name !== 'prompt') {
                    $this->parameters[$parameter->name] = $parameter->default;
                }
            }
        }
    }

    public function updatedSelectedModel()
    {
        $this->initializeModelParameters();
    }

    public function generate()
    {
        Log::info('Generate started');

        if (empty($this->prompt)) {
            Log::info('Empty prompt, returning');
            return;
        }

        $this->isGenerating = true;
        Log::info('Parameters:', [
            'prompt' => $this->prompt,
            'selectedModel' => $this->selectedModel,
            'parameters' => $this->parameters
        ]);

        try {
            $replicate = new ReplicateService();
            $model = $replicate->getModel($this->selectedModel);

            if (!$model) {
                throw new \RuntimeException("Modèle non trouvé");
            }

            // Validation des paramètres
            $validationRules = $model->getValidationRules();
            $promptRules = $validationRules['prompt'] ?? ['required', 'string', 'min:3'];
            unset($validationRules['prompt']);

            $prefixedRules = array_combine(
                array_map(fn($key) => "parameters.{$key}", array_keys($validationRules)),
                array_values($validationRules)
            );

            $validatedData = $this->validate([
                'prompt' => $promptRules,
                ...$prefixedRules
            ]);

            Log::info('Validation passed');

            // Création de la génération en base
            $this->currentGeneration = Generation::create([
                'user_id' => Auth::id(),
                'prompt' => $this->prompt,
                'model' => $model->getName(),
                'version' => $model->getVersion(),
                'parameters' => array_merge(['prompt' => $this->prompt], $this->parameters),
                'status' => 'pending'
            ]);

            Log::info('Generation created in DB', ['id' => $this->currentGeneration->id]);

            // Appel à Replicate
            $parameters = array_merge(['prompt' => $this->prompt], $this->parameters);
            $filteredParameters = array_filter($parameters, function($value) {
                return $value !== null;
            });

            $result = $replicate->generate(
                $this->selectedModel,
                $filteredParameters
            );

            Log::info('Replicate API response:', ['result' => $result]);

            // Mise à jour avec l'ID de prédiction
            $this->currentGeneration->update([
                'prediction_id' => $result['id'],
                'status' => $result['status'],
                'result' => $result
            ]);

            // Si on a déjà un résultat (cas synchrone)
            if (isset($result['output']) && is_array($result['output'])) {
                $this->currentGeneration->update([
                    'status' => 'succeeded',
                    'image_url' => $result['output'][0] ?? null
                ]);
                $this->isGenerating = false;
            }

            if (isset($result['urls']['get'])) {
                $this->dispatch('generation-started', generationId: $this->currentGeneration->id);
            }

        } catch (\Exception $e) {
            Log::error('Generation error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('generation', "Erreur lors de la génération : {$e->getMessage()}");
            if ($this->currentGeneration) {
                $this->currentGeneration->update(['status' => 'failed']);
            }
            $this->isGenerating = false;
        }
    }

    public function render()
    {
        $replicate = new ReplicateService();
        return view('livewire.studio', [
            'models' => $replicate->getAvailableModels(),
        ]);
    }

    public function handleGenerationCompleted(array $data = [])
    {
        $this->currentGeneration->refresh();
        if ($this->currentGeneration->status === 'succeeded') {
            $this->isGenerating = false;
        }
    }

    public function handleGenerationFailed(array $data = [])
    {
        $this->currentGeneration->refresh();
        $this->isGenerating = false;
        $this->addError('generation', 'La génération a échoué : ' . ($this->currentGeneration->result['error'] ?? 'Erreur inconnue'));
    }
}
