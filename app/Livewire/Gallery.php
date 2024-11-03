<?php

namespace App\Livewire;

use App\Models\Generation;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class Gallery extends Component
{
    use WithPagination;

    public $generationIdToDelete;

    public function deleteGeneration()
    {
        $generation = Generation::find($this->generationIdToDelete);
        if ($generation && $generation->user_id === auth()->id()) {
            // Supprimer le fichier physique
            Storage::delete($generation->local_image_path);
            // Supprimer l'enregistrement
            $generation->delete();
        }
        $this->generationIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.gallery', [
            'generations' => Generation::where('user_id', auth()->id())
                ->where('status', 'succeeded')
                ->whereNotNull('local_image_path')
                ->latest()
                ->paginate(12)
        ]);
    }
}
