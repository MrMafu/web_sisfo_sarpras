@props([
    'target' => '',
    'title'  => 'Details',
    'fields' => [],
    'item'   => null,
])

<div
    x-show="{{ $target }}.open"
    x-cloak
    x-transition.opacity
    class="z-50 fixed inset-0 bg-black/50 flex items-center justify-center">
    <div
        @click.outside="{{ $target }}.open = false"
        x-show="{{ $target }}.open"
        x-transition
        class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button @click="{{ $target }}.open = false"
            class="cursor-pointer absolute top-6 right-6 text-gray-500 hover:text-gray-700 transition
            ease-in-out">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h2 class="text-2xl font-semibold mb-4">
            {{ $title }} #<span x-text="{{ $target }}.item.id"></span>
        </h2>

        <div class="space-y-3">
            @foreach ($fields as $field => $label)
                <div class="block">
                    <p class="font-semibold">{{ $label }}:</p>
                    @if ($field === 'image')
                        <img x-bind:src="{{ $target }}.item['{{ $field }}']"
                        class="w-16 h-16 object-cover rounded"
                        alt="Item Image">
                    @elseif (in_array($field, ['created_at', 'updated_at']))
                        <span x-text="formatDateTime({{ $target }}.item['{{ $field }}'])"></span>
                    @else
                        <span x-text="{{ $target }}.item['{{ $field }}']"></span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6 text-right">
            <button @click="{{ $target }}.open = false"
                class="cursor-pointer px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition
                duration-200 ease-in-out">
                Close
            </button>
        </div>
    </div>
</div>