<div>
    <div class="messages">
        @foreach ($messages as $message)
            <div class="{{ $message['role'] }}">
                @if (is_array($message['content']))
                    @foreach ($message['content'] as $content)
                        @if ($content['type'] === 'text')
                            <p>{{ $content['text'] }}</p>
                        @elseif ($content['type'] === 'image_url')
                            <img src="{{ $content['image_url']['url'] }}" alt="Image">
                        @endif
                    @endforeach
                @else
                    {{ $message['content'] }}
                @endif
            </div>
        @endforeach
    </div>

    <div class="streamed-response">
        {{ $streamedResponse }}
    </div>

    <form wire:submit.prevent="sendMessage">
        <input type="text" wire:model="newMessage" placeholder="Entrez votre message">
        <input type="text" wire:model="imageUrl" placeholder="URL de l'image (optionnel)">
        <select wire:model="selectedModel">
            @foreach ($models as $modelId => $contextLength)
                <option value="{{ $modelId }}">{{ $modelId }} (Max tokens: {{ $contextLength }})</option>
            @endforeach
        </select>
        <input type="range" wire:model="temperature" min="0" max="2" step="0.1">
        <span>Temperature: {{ $temperature }}</span>
        <button type="submit">Envoyer</button>
    </form>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('updateStreamedResponse', (response) => {
            document.querySelector('.streamed-response').innerHTML = response;
        });
    });
</script>
