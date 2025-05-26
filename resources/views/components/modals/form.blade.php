@props([
    'target'   => '',
    'title'    => 'Form',
    'method'   => 'POST',
    'action'   => '#',
    'fields'   => [],
    'item'     => null,
    'errorBag' => 'default',
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

        <h2 class="text-2xl font-semibold mb-6">{{ $title }}</h2>

        <form method="POST"
            x-bind:action="{{ $target }}.item ? '{{ $action }}'.replace('REPLACE', {{ $target }}.item.id) : '#'"
            class="space-y-5"
            enctype="multipart/form-data">
            @csrf
            @if (!in_array($method, ['GET', 'POST']))
                @method($method)
            @endif

            @foreach ($fields as $field => $config)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $config['label'] ?? ucfirst($field) }}
                    </label>

                    @if (($config['type'] ?? null) === 'select')
                        <select name="{{ $field }}"
                            class="w-full border border-gray-300 rounded-md px-4 py-2
                            focus:outline-none focus:border-[#7752fe] transition duration-200
                            @error($field, $errorBag) border-red-600 @enderror"
                            {{ $config['required'] ?? false ? 'required' : '' }}>
                            @foreach ($config['options'] as $value => $text)
                                <option value="{{ $value }}"
                                    :selected="{{ $target }}.item ? {{ $target }}.item.{{ $field }} === '{{ $value }}' : false">
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    @elseif (($config['type'] ?? null) === 'file')
                        <input
                            type="file"
                            name="{{ $field }}"
                            accept="image/*"
                            class="w-full border-dashed border border-gray-300 rounded-md px-4 py-3
                            file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                            file:bg-[#7752fe] file:text-white file:cursor-pointer
                            hover:file:bg-[#6b4ae5] transition duration-200 ease-in-out
                            @error($field, $errorBag) border-red-600 @enderror"
                            {{ $config['required'] ?? false ? 'required' : '' }}>
                    @else
                        <input
                            type="{{ $config['type'] ?? 'text' }}"
                            name="{{ $field }}"
                            x-model="{{ $target }}.item.{{ $field }}"
                            class="w-full border border-gray-300 rounded-md px-4 py-2
                            focus:outline-none focus:border-[#7752fe] transition duration-200
                            @error($field, $errorBag) border-red-600 @enderror"
                            {{ $config['required'] ?? false ? 'required' : '' }}>
                    @endif

                    @error($field, $errorBag)
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" @click="{{ $target }}.open = false"
                    class="cursor-pointer px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition
                    duration-200 ease-in-out">
                    Cancel
                </button>

                <button type="submit"
                    class="cursor-pointer px-4 py-2 bg-[#7752fe] text-white rounded-md hover:bg-[#6b4ae5]
                    transition duration-200 ease-in-out">
                    {{ $submitText ?? 'Save' }}
                </button>
            </div>
        </form>
    </div>
</div>