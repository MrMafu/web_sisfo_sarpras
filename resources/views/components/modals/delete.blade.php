@props([
    'target' => '',
    'open'   => false,
    'title'  => 'Confirm Deletion',
])

<div
    x-show="{{ $target }}.open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div @click.outside="{{ $target }}.open = false" x-transition
        class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6">
        <h2 class="text-xl font-semibold mb-4">{{ $title }}</h2>

        <div class="flex justify-center mb-4">
            <i class="fa-solid fa-circle-exclamation text-red-600 text-6xl"></i>
        </div>

        <p class="mb-4">{{ $slot }}</p>

        {{-- Buttons --}}
        <div class="flex justify-end space-x-3">
            <button @click="{{ $target }}.open = false"
                class="cursor-pointer px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition
                duration-200 ease-in-out">
                Cancel
            </button>

            {{ $actions }}
        </div>
    </div>
</div>