@props(['text'])

<div x-data="{ show: false }" class="inline-block relative">
    <button @mouseenter="show = true" @mouseleave="show = false" type="button" class="text-gray-400 hover:text-gray-500">
        <svg class="h-4 w-4 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>

    <div x-show="show" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="fixed z-[9999] w-64 px-4 py-2 text-sm text-gray-600 bg-white rounded-lg shadow-lg border border-gray-200"
        x-cloak @mouseenter="show = true" @mouseleave="show = false" x-init="$el.style.left = ($el.previousElementSibling.getBoundingClientRect().right + 10) + 'px';
        $el.style.top = ($el.previousElementSibling.getBoundingClientRect().top - 10) + 'px';">
        {{ $text }}
    </div>
</div>
