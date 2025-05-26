<form method="GET" action="{{ $action }}"
    id="search-sort-form"
    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
    @foreach (request()->except(['search', 'sort', 'direction', ...$exclude]) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    {{-- Search --}}
    <div class="flex-1 flex items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search {{ $searchName }}…"
            class="flex-1 bg-white border border-gray-300 rounded-md px-4 py-2
            focus:outline-none focus:border-[#7752fe] transition duration-200 ease-in-out" />
    </div>

    {{-- Sort --}}
    <div class="flex items-center gap-3">
        <label class="text-sm font-medium text-gray-700">Sort by</label>
        <select name="sort"
            class="bg-white border border-gray-300 rounded-md px-4 py-2 text-sm
            focus:outline-none focus:border-[#7752fe] transition duration-200 ease-in-out">
            @foreach ($sortOptions as $value => $label)
                <option value="{{ $value }}" {{ request('sort') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select name="direction"
            class="bg-white border border-gray-300 rounded-md px-4 py-2 text-sm
            focus:outline-none focus:border-[#7752fe] transition duration-200 ease-in-out">
            <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
            <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Descending</option>
        </select>
    </div>

    {{-- Filters --}}
    {!! $filters ?? '' !!}
</form>