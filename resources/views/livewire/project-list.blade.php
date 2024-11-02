<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 pt-24" x-data="{ setProjectToDelete: id => $wire.projectIdToDelete = id }">
    <div class="flex justify-between items-center mb-8 space-x-10">
        <h2 class="text-2xl font-bold text-gray-900">Mes Conversations</h2>
        <a href="{{ route('chat.create') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nouvelle Conversation
        </a>
    </div>

    @if ($projects->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune conversation</h3>
            <p class="mt-1 text-sm text-gray-500">Commencez par créer une nouvelle conversation.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($projects as $project)
                <div
                    class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-150 overflow-hidden border border-gray-100">
                    <a href="{{ route('chat', $project) }}" class="block p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 truncate">
                                {{ $project->name }}
                            </h3>
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Modifié {{ $project->updated_at->diffForHumans() }}
                        </div>
                    </a>
                    <button x-data=""
                        x-on:click.prevent="setProjectToDelete({{ $project->id }}); $dispatch('open-modal', 'confirm-project-deletion')"
                        class="w-full px-6 py-2 border-t border-gray-100 text-sm text-red-600 hover:text-red-900 transition-colors duration-150 flex items-center hover:bg-red-50">
                        <x-untitledui-trash class="w-4 h-4 mr-1" />
                        Supprimer
                    </button>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $projects->links() }}
        </div>
    @endif

    <x-modal name="confirm-project-deletion" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Êtes-vous sûr de vouloir supprimer cette conversation ?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Une fois supprimée, cette conversation et tous ses messages seront définitivement effacés.') }}
            </p>

            <div class="mt-6 flex justify-end space-x-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Annuler') }}
                </x-secondary-button>

                <x-danger-button wire:click="deleteProject" x-on:click="$dispatch('close')">
                    {{ __('Supprimer la conversation') }}
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</div>
