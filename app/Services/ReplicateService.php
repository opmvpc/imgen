<?php

namespace App\Services;

use App\Services\Replicate\Contracts\ReplicateModelInterface;
use App\Services\Replicate\Models\FluxModel;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReplicateService
{
    private string $apiToken;
    private string $apiUrl;

    /** @var array<string, ReplicateModelInterface> */
    private array $models = [];

    public function __construct()
    {
        $this->apiToken = Config::get('services.replicate.token');
        $this->apiUrl = Config::get('services.replicate.api_url');

        if (empty($this->apiToken)) {
            throw new RuntimeException('Replicate API token not configured');
        }

        // Enregistrement des modèles disponibles
        $this->registerModel(new FluxModel());
        // Ajouter d'autres modèles ici quand nécessaire
    }

    private function registerModel(ReplicateModelInterface $model): void
    {
        $this->models[$model->getName()] = $model;
    }

    /**
     * Génère une image avec le modèle spécifié
     *
     * @param string $modelName Nom du modèle (ex: 'black-forest-labs/flux-1.1-pro')
     * @param array $parameters Paramètres pour la génération
     * @param bool $waitForResult Si true, attend le résultat (max 60s)
     * @return array Données de la prédiction
     * @throws RuntimeException Si le modèle n'existe pas
     */
    public function generate(string $modelName, array $parameters, bool $waitForResult = false): array
    {
        if (!isset($this->models[$modelName])) {
            throw new RuntimeException("Model '$modelName' not found");
        }

        $model = $this->models[$modelName];

        // On n'attend jamais le résultat, on utilisera le polling à la place
        $response = $this->getHttpClient()
            ->post('predictions', [
                'version' => $model->getVersion(),
                'input' => $parameters,
                'webhook_completed' => Config::get('services.replicate.webhook_url'),
            ]);

        return $this->handleResponse($response);
    }

    /**
     * Récupère le résultat d'une prédiction
     */
    public function getResult(string $predictionId): array
    {
        $response = $this->getHttpClient()
            ->get("predictions/{$predictionId}");

        return $this->handleResponse($response);
    }

    /**
     * Annule une prédiction en cours
     */
    public function cancelPrediction(string $predictionId): array
    {
        $response = $this->getHttpClient()
            ->post("predictions/{$predictionId}/cancel");

        return $this->handleResponse($response);
    }

    /**
     * Liste toutes les prédictions
     */
    public function listPredictions(): array
    {
        $response = $this->getHttpClient()
            ->get('predictions');

        return $this->handleResponse($response);
    }

    /**
     * Retourne le client HTTP configuré
     */
    private function getHttpClient()
    {
        return Http::withToken($this->apiToken)
            ->baseUrl($this->apiUrl)
            ->timeout(10) // Timeout plus court pour la requête initiale
            ->connectTimeout(5); // Timeout de connexion plus court
    }

    /**
     * Gère la réponse de l'API
     *
     * @throws RuntimeException Si la requête échoue
     */
    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $error = $response->json('error.message') ?? $response->body();
        throw new RuntimeException("Replicate API error: {$error}", $response->status());
    }

    /**
     * Retourne un modèle spécifique
     */
    public function getModel(string $modelName): ?ReplicateModelInterface
    {
        return $this->models[$modelName] ?? null;
    }

    /**
     * Retourne la liste des modèles disponibles
     *
     * @return array<string, ReplicateModelInterface>
     */
    public function getAvailableModels(): array
    {
        return $this->models;
    }

    public function checkPrediction(string $predictionId): array
    {
        $response = $this->getHttpClient()
            ->get("predictions/{$predictionId}");

        return $this->handleResponse($response);
    }
}
