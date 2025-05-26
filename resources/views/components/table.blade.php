@props([
    'items'         => [],
    'headers'       => [],
    'sortField'     => null,
    'sortDirection' => 'asc',
    'emptyMessage'  => 'No records found.',
    'actions'       => false,
    'actionProps'   => [],
])

<div class="overflow-auto rounded-lg border border-gray-200 shadow-xs relative">
    <div class="min-w-full inline-block align-middle">
        <table class="min-w-full divide-y divide-gray-200">
            {{-- Table Head --}}
            <thead class="bg-[#f1eeff] sticky top-0 z-10">
                <tr>
                    @foreach ($headers as $field => $label)
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide
                            {{ $sortField === $field ? 'text-[#7752fe]' : 'text-gray-600' }}">
                            {{ $label }}
                        </th>
                    @endforeach
                    @if ($actions)
                        <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wide">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            {{-- Table Body --}}
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50 transition duration-200 ease-in-out">
                        @foreach ($headers as $field => $label)
                            <td class="px-6 py-3 whitespace-nowrap text-gray-700">
                                @if ($field === 'image')
                                    <img src="{{ data_get($item, 'image') }}"
                                    class="object-cover rounded" alt="Item Image">
                                @else
                                    {{ data_get($item, $field) }}
                                @endif
                            </td>
                        @endforeach
                        @if ($actions)
                            <td class="px-6 py-3 whitespace-nowrap text-center">
                                <x-actions
                                    :item="$item"
                                    :viewFields="$actionProps['viewFields'] ?? ['id']"
                                    :editFields="$actionProps['editFields'] ?? ['id']"
                                    :deleteRoute="$actionProps['deleteRoute'] ?? null"
                                />
                            </td>
                        @endif
                    </tr>
                {{-- No Records Result --}}
                @empty
                    <tr>
                        <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}"
                            class="py-8 text-center text-gray-500">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>