<div class="flex w-full">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white border-r border-gray-100 fixed top-16 bottom-0 left-0 overflow-y-auto">
        <div class="p-4 space-y-4">
            <!-- Ajout du titre -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 truncate">
                    {{ $project->name }}
                </h2>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modèle</label>
                <div class="relative">
                    <select wire:model.live="selectedModel"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($models as $model)
                            <option value="{{ $model['id'] }}">{{ $model['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-0 -bottom-5">
                        <x-action-message on="model-updated" class="text-xs text-green-500">
                            Sauvegardé
                        </x-action-message>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Température: {{ $temperature }}
                </label>
                <div class="relative">
                    <input type="range" wire:model.live="temperature" min="0" max="2" step="0.1"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <div class="absolute right-0 -bottom-5">
                        <x-action-message on="temperature-updated" class="text-xs text-green-500">
                            Sauvegardé
                        </x-action-message>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-gray-50 ml-[25%] pt-16 pb-6" x-data="{ inputHeight: '2.5rem' }">
        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages"
            :style="'padding-bottom: calc(' + inputHeight + ' + 2rem)'">
            @foreach ($messages as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="flex flex-col {{ $message['role'] === 'user' ? 'items-end' : 'items-start' }} max-w-[70%]">
                        <span class="text-xs text-gray-500 mb-1">
                            {{ $message['role'] === 'user' ? 'Vous' : 'Assistant' }}
                        </span>
                        <div
                            class="w-full prose {{ $message['role'] === 'user' ? 'bg-indigo-500 text-white prose-invert' : 'bg-white' }} rounded-lg px-4 shadow break-words prose-headings:mt-4">

                            <div class="text-sm " x-data
                                x-html="marked.parse($el.textContent.trim()).replace(/^<br>/, '')">
                                {{ $message['content'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach


            <div wire:loading class="flex justify-start">
                <div class="flex flex-col items-start">
                    <span class="text-xs text-gray-500 mb-1">Assistant</span>
                    <div class="max-w-[70%] bg-white rounded-lg p-4 shadow">
                        <p id="streaming-response" class="text-sm prose" wire:stream="streamedResponse">
                        </p>
                        <div id="loading-indicator" class="flex items-center gap-2">
                            <x-untitledui-loading-01 class="animate-spin h-4 w-4 text-indigo-500" />
                            <span class="text-gray-500">L'assistant réfléchit...</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-100 p-4 bg-white fixed bottom-0 right-0 left-[25%]">
            <form wire:submit.prevent="sendMessage" class="flex gap-4">
                <div class="flex-1 space-y-2">
                    <textarea wire:model="newMessage" placeholder="Entrez votre message" rows="1"
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
                        @input="resize()" @keydown.ctrl.enter.prevent="$wire.sendMessage()"></textarea>
                </div>

                <button type="submit"
                    class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-akar-send class="w-6 h-6" />
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        const streamingResponse = document.getElementById('streaming-response');
        const loadingIndicator = document.getElementById('loading-indicator');
        const chatMessages = document.getElementById('chat-messages');
        const SCROLL_OFFSET = 100;

        // Écouter les changements de hauteur de l'input
        window.addEventListener('input-resized', (event) => {
            chatMessages.style.paddingBottom = `calc(${event.detail.height} + 2rem)`;
            scrollToBottom();
        });

        const scrollToBottom = () => {
            window.scrollTo({
                top: document.documentElement.scrollHeight + SCROLL_OFFSET,
                behavior: 'smooth'
            });
        };

        window.addEventListener('messageAdded', () => {
            console.log('messageAdded');

            setTimeout(scrollToBottom, 500);
        });

        setTimeout(scrollToBottom, 500);

        const observer = new MutationObserver(() => {
            scrollToBottom();
            const hasContent = streamingResponse.textContent.trim().length > 0;
            loadingIndicator.style.display = hasContent ? 'none' : 'flex';
        });

        observer.observe(streamingResponse, {
            childList: true,
            characterData: true,
            subtree: true
        });
    });
</script>
