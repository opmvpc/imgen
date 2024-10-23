<div class="flex w-full">
    <!-- Sidebar -->
    <div class="w-1/4 bg-white border-r border-gray-100 overflow-y-auto">
        <div class="p-4 space-y-4">
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
    <div class="flex-1 flex flex-col bg-gray-50">
        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            @foreach ($messages as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="flex flex-col {{ $message['role'] === 'user' ? 'items-end' : 'items-start' }} max-w-[70%]">
                        <span class="text-xs text-gray-500 mb-1">
                            {{ $message['role'] === 'user' ? 'Vous' : 'Assistant' }}
                        </span>
                        <div
                            class="w-full {{ $message['role'] === 'user' ? 'bg-indigo-500 text-white' : 'bg-white' }} rounded-lg p-4 shadow break-words">
                            @if (is_array($message['content']))
                                @foreach ($message['content'] as $content)
                                    @if ($content['type'] === 'text')
                                        <p class="text-sm whitespace-pre-wrap">{{ $content['text'] }}</p>
                                    @elseif ($content['type'] === 'image_url')
                                        <img src="{{ $content['image_url']['url'] }}" alt="Image"
                                            class="rounded-lg max-w-full h-auto my-2">
                                    @endif
                                @endforeach
                            @else
                                <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($streamedResponse)
                <div class="flex justify-start">
                    <div class="flex flex-col items-start">
                        <span class="text-xs text-gray-500 mb-1">Assistant</span>
                        <div class="max-w-[70%] bg-white rounded-lg p-4 shadow">
                            <p class="text-sm">{{ $streamedResponse }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-100 p-4 bg-white">
            <form wire:submit.prevent="sendMessage" class="flex gap-4">
                <div class="flex-1 space-y-2">
                    <textarea
                        wire:model="newMessage"
                        placeholder="Entrez votre message"
                        rows="1"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                        style="min-height: 2.5rem; max-height: 15rem; overflow-y: hidden;"
                        x-data="{
                            resize: function() {
                                $el.style.height = '2.5rem';
                                $el.style.height = $el.scrollHeight + 'px';
                                if ($el.scrollHeight > 240) {
                                    $el.style.overflowY = 'auto';
                                } else {
                                    $el.style.overflowY = 'hidden';
                                }
                            }
                        }"
                        x-init="resize()"
                        @input="resize()"
                        @keydown.ctrl.enter.prevent="$wire.sendMessage()"
                    ></textarea>
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
        @this.on('updateStreamedResponse', (response) => {
            const messagesContainer = document.querySelector('.overflow-y-auto');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });
    });
</script>
