<div class="flex items-center gap-3">
    @if (isset($label))
        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    <select name="{{ $name }}"
        class="bg-white border border-gray-300 rounded-md px-4 py-2 text-sm
        focus:outline-none focus:border-[#7752fe] transition duration-200 ease-in-out">
        <option value="">{{ $defaultValue ?? 'All' }}</option>
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" {{ request($name) == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
    </select>
</div>