<div class="flex w-full">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white border-r border-gray-100 fixed top-16 bottom-0 left-0 overflow-y-auto">
        <div class="p-4 space-y-4">
            <!-- Sélection du modèle -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modèle</label>
                <div class="relative">
                    <select wire:model.live="selectedModel"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($models as $model)
                            <option value="{{ $model->getName() }}">{{ $model->getName() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Paramètres du modèle -->
            <div class="space-y-4">
                @php
                    $model = app(App\Services\ReplicateService::class)->getModel($selectedModel);
                @endphp

                @foreach ($model->getParameters() as $parameter)
                    @if ($parameter->name !== 'prompt')
                        <div>
                            <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-1">
                                {{ ucfirst($parameter->name) }}
                                @if ($parameter->description)
                                    <x-tooltip :text="$parameter->description" />
                                @endif
                            </label>

                            @switch($parameter->type->value)
                                @case('boolean')
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model.live="parameters.{{ $parameter->name }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                @break

                                @case('string')
                                    @if (isset($parameter->validation['enum']))
                                        <select wire:model.live="parameters.{{ $parameter->name }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            @foreach ($parameter->validation['enum'] as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" wire:model.live="parameters.{{ $parameter->name }}"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @endif
                                @break

                                @case('integer')
                                @case('float')
                                    <input type="number" wire:model.live="parameters.{{ $parameter->name }}"
                                        @if (isset($parameter->validation['min'])) min="{{ $parameter->validation['min'] }}" @endif
                                        @if (isset($parameter->validation['max'])) max="{{ $parameter->validation['max'] }}" @endif
                                        step="{{ $parameter->type->value === 'float' ? '0.1' : '1' }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @break
                            @endswitch
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col bg-gray-50 ml-[25%] pt-16 pb-6" x-data="{ inputHeight: '2.5rem' }">
        <!-- Zone d'affichage des images -->
        <div class="flex-1 p-4 overflow-y-auto" id="generated-images">
            @if ($isGenerating)
                <div class="flex items-center justify-center h-full">
                    <div class="relative" x-data="{
                        startTime: Date.now(),
                        elapsedTime: '0.00',
                        updateTimer() {
                            requestAnimationFrame(() => {
                                if (this.$wire.isGenerating) {
                                    const elapsed = (Date.now() - this.startTime) / 1000;
                                    this.elapsedTime = elapsed.toFixed(2);
                                    this.updateTimer();
                                }
                            });
                        }
                    }" x-init="updateTimer()">
                        <!-- Cercle qui pulse autour - Maintenant parfaitement rond -->
                        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40">
                            <div class="absolute inset-0 animate-ping rounded-full bg-indigo-400 opacity-20"></div>
                        </div>

                        <!-- Icône qui pulse -->
                        <div class="animate-pulse relative">
                            <svg class="w-32 h-32 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>

                        <!-- Texte en dessous - Layout stabilisé -->
                        <div class="mt-4 text-center text-indigo-500 font-medium">
                            <span class="block">Génération en cours...</span>
                            <span class="tabular-nums block mt-1" x-text="elapsedTime + 's'"></span>
                        </div>
                    </div>
                </div>
            @elseif($currentGeneration)
                @if ($currentGeneration->status === 'succeeded' && $currentGeneration->image_url)
                    <div class="relative group animate-fade-in">
                        <img src="{{ $currentGeneration->image_url }}" alt="{{ $currentGeneration->prompt }}"
                            class="max-w-full rounded-lg shadow-lg transition-transform duration-200 ease-in-out group-hover:scale-[1.01]">

                        <div
                            class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <p class="text-white">{{ $currentGeneration->prompt }}</p>
                        </div>
                    </div>
                @elseif($currentGeneration->status === 'failed')
                    <div class="p-4 bg-red-50 text-red-700 rounded-lg">
                        Une erreur est survenue lors de la génération
                    </div>
                @endif
            @else
                <div class="flex items-center justify-center h-full text-gray-400">
                    <div class="text-center">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <p class="mt-4">Entrez un prompt pour générer une image</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Zone de saisie -->
        <div class="border-t border-gray-100 p-4 bg-white fixed bottom-0 right-0 left-[25%]">
            <form wire:submit.prevent="generate" class="flex gap-4">
                <div class="flex-1">
                    <textarea wire:model="prompt" placeholder="Décrivez l'image que vous souhaitez générer..." rows="1"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                        style="min-height: 2.5rem; max-height: 15rem; overflow-y: hidden;" x-data="{
                            resize: function() {
                                $el.style.height = '2.5rem';
                                $el.style.height = $el.scrollHeight + 'px';
                                if ($el.scrollHeight > 240) {
                                    $el.style.overflowY = 'auto';
                                } else {
                                    $el.style.overflowY = 'hidden';
                                }
                                $dispatch('input-resized', { height: $el.style.height });
                            }
                        }" x-init="resize()"
                        @input="resize()"></textarea>
                    @error('generation')
                        <div class="mt-1 text-red-600 text-sm">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" x-data="{ isGenerating: $wire.isGenerating }" x-init="$watch('$wire.isGenerating', value => isGenerating = value)" x-bind:disabled="isGenerating"
                    class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-sparkles class="w-6 h-6" x-bind:class="{ 'animate-spin': isGenerating }" />
                        <span x-text="isGenerating ? 'Génération...' : 'Générer'"></span>
                    </div>
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('livewire:initialized', () => {
        const generatedImages = document.querySelector('.flex-1.p-4.overflow-y-auto');
        const SCROLL_OFFSET = 100;

        window.addEventListener('input-resized', (event) => {
            if (generatedImages) {
                generatedImages.style.paddingBottom = `calc(${event.detail.height} + 2rem)`;
            }
        });

        // Ajout de la gestion du polling
        Livewire.on('generation-started', async (event) => {
            const checkProgress = async () => {
                try {
                    const response = await fetch(`/replicate/check/${event.generationId}`);
                    if (!response.ok) {
                        if (response.status === 504 || response.status === 408) {
                            setTimeout(checkProgress, 100);
                            return;
                        }
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();

                    if (data.status === 'succeeded') {
                        await fetch(`/replicate/update/${event.generationId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(data)
                        });

                        Livewire.dispatch('generation-completed', {
                            generationId: event.generationId
                        });
                        return;
                    } else if (data.status === 'failed') {
                        Livewire.dispatch('generation-failed', {
                            generationId: event.generationId
                        });
                        return;
                    }

                    setTimeout(checkProgress, 250);
                } catch (error) {
                    console.error('Error checking generation progress:', error);
                    setTimeout(checkProgress, 5000);
                }
            };

            checkProgress();
        });
    });
</script>
