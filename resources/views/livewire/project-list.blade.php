<div>
    <h2 class="text-2xl font-bold mb-4">Mes Conversations</h2>
    @if ($projects->isEmpty())
        <p>Vous n'avez pas encore de conversations.</p>
    @else
        <ul>
            @foreach ($projects as $project)
                <li class="mb-2">
                    <a href="{{ route('chat', $project) }}" class="text-blue-600 hover:underline">
                        {{ $project->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
    <a href="{{ route('chat.create') }}" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded">
        Nouvelle Conversation
    </a>
</div>
