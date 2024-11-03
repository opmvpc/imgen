<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24"
     x-data="{
        setGenerationToDelete: id => $wire.generationIdToDelete = id,
        showFullscreen: false,
        currentIndex: 0,
        images: {{ json_encode($generations->map(fn($g) => [
            'url' => Storage::url($g->local_image_path),
            'prompt' => $g->prompt,
            'date' => $g->created_at->diffForHumans()
        ])) }},
        next() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        },
        prev() {
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        },
        closeOnEscape(e) {
            if (e.key === 'Escape') this.showFullscreen = false;
            if (e.key === 'ArrowRight') this.next();
            if (e.key === 'ArrowLeft') this.prev();
        }
     }"
     @keydown.window="closeOnEscape">
    <div class="flex justify-between items-center mb-8 space-x-10">
        <h2 class="text-2xl font-bold text-gray-900">Ma Galerie</h2>
        <a href="{{ route('studio') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle Image
        </a>
    </div>

    @if ($generations->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune image</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par générer une image dans le studio.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($generations as $index => $generation)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-150 overflow-hidden border border-gray-100">
                    <div class="cursor-pointer" @click="showFullscreen = true; currentIndex = {{ $index }}">
                        <img src="{{ Storage::url($generation->local_image_path) }}"
                             alt="{{ $generation->prompt }}"
                             class="w-full h-48 object-cover">
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-900 line-clamp-2 mb-2">
                            {{ $generation->prompt }}
                        </p>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $generation->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <button x-data=""
                        x-on:click.prevent="setGenerationToDelete({{ $generation->id }}); $dispatch('open-modal', 'confirm-generation-deletion')"
                        class="w-full px-4 py-2 border-t border-gray-100 text-sm text-red-600 hover:text-red-900 transition-colors duration-150 flex items-center justify-center hover:bg-red-50">
                        <x-untitledui-trash class="w-4 h-4 mr-1" />
                        Supprimer
                    </button>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $generations->links() }}
        </div>

        <!-- Vue plein écran -->
        <div x-show="showFullscreen"
             x-transition
             class="fixed inset-0 bg-black/90 z-50 flex flex-col"
             @click.self="showFullscreen = false">

            <!-- Barre de navigation supérieure -->
            <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-center text-white z-10">
                <button @click="showFullscreen = false" class="p-2 hover:bg-white/10 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Zone principale de l'image -->
            <div class="flex-1 flex items-center justify-center relative">
                <!-- Bouton précédent -->
                <button @click="prev"
                        class="absolute left-4 p-2 text-white hover:bg-white/10 rounded-full transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Image -->
                <img :src="images[currentIndex].url"
                     :alt="images[currentIndex].prompt"
                     class="max-h-[80vh] max-w-[90vw] object-contain">

                <!-- Bouton suivant -->
                <button @click="next"
                        class="absolute right-4 p-2 text-white hover:bg-white/10 rounded-full transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Informations en bas -->
            <div class="bg-black/50 backdrop-blur-sm text-white p-6">
                <p class="text-lg mb-2" x-text="images[currentIndex].prompt"></p>
                <p class="text-sm text-gray-300" x-text="images[currentIndex].date"></p>
            </div>
        </div>
    @endif

    <x-modal name="confirm-generation-deletion" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Êtes-vous sûr de vouloir supprimer cette image ?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Une fois supprimée, cette image sera définitivement effacée.') }}
            </p>

            <div class="mt-6 flex justify-end space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Annuler') }}
                </x-secondary-button>

                <x-danger-button wire:click="deleteGeneration" x-on:click="$dispatch('close')">
                    {{ __('Supprimer l\'image') }}
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</div>
