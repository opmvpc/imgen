<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function downloadAndSave(string $url): string
    {
        try {
            $response = Http::timeout(30)
                ->withoutVerifying() // Pour éviter les problèmes de certificats
                ->get($url);

            if (!$response->successful()) {
                throw new \RuntimeException("Impossible de télécharger l'image: " . $response->status());
            }

            $extension = $this->getExtensionFromUrl($url) ?? 'jpg';
            $filename = date('Y/m/d/') . Str::random(40) . '.' . $extension;
            $path = 'generations/' . $filename;

            // Création du répertoire si nécessaire
            $directory = Storage::disk('public')->path(dirname($path));
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            if (!Storage::disk('public')->put($path, $response->body())) {
                throw new \RuntimeException("Impossible de sauvegarder l'image");
            }

            return $path;
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement de l\'image', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getExtensionFromUrl(string $url): ?string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp'])
            ? strtolower($extension)
            : 'jpg';
    }
}
