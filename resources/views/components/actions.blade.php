@props([
    'item'        => null,
    'viewFields'  => ['id'],
    'editFields'  => ['id'],
    'deleteRoute' => null,
    'showRoute'   => null,
])

@php
    $isBorrowing = $showRoute === 'borrowings.show';
    $isReturning = $showRoute === 'returnings.show';
@endphp

<div x-data="{
    open          : false,
    dropdownTop   : 0,
    dropdownRight : 0,
}"
    class="relative inline-block">
    {{-- Trigger --}}
    <button
        @click="
            open = !open;
            $nextTick(() => {
                const rect    = $event.target.getBoundingClientRect();
                dropdownTop   = rect.bottom + window.scrollY;
                dropdownRight = window.innerWidth - rect.right;
            });
        "
        class="cursor-pointer inline-flex items-center justify-center p-1 border rounded bg-white
        text-gray-500 border-gray-300 hover:bg-gray-50 hover:text-gray-700 hover:border-gray-400
        focus:outline-none transition duration-200 ease-in-out">
        <i class="fa-solid fa-ellipsis"></i>
    </button>

    <template x-teleport="body">
        {{-- Actions Dropdown --}}
        <div x-show="open" @click.outside="open = false" x-transition
            class="flex flex-col fixed w-32 bg-white border border-gray-200 rounded-md shadow z-50"
            :style="`
                top   : ${dropdownTop}px;
                right : ${dropdownRight}px;
            `">
            @if ($isBorrowing || $isReturning)
                {{-- Details Link --}}
                <a href="{{
                    $isBorrowing ?
                    route('borrowings.show', $item->id) :
                    route('returnings.show', $item->id)
                }}"
                    class="cursor-pointer flex items-center gap-2 w-full px-4 py-2 text-sm text-[#7752fe]
                    hover:text-[#6b4ae5] hover:bg-[#f1eeff] transition duration-200 ease-in-out">
                    <i class="fa-solid fa-file-invoice w-4 text-center"></i>
                    <span>Details</span>
                </a>
            @else
                {{-- Details --}}
                <button
                    @click="
                        $dispatch('view-item', {{ json_encode(collect($viewFields)->mapWithKeys(fn($field) => [
                                $field => data_get($item, $field)
                            ])->all()) }});
                        open = false
                    "
                    class="cursor-pointer flex items-center gap-2 w-full px-4 py-2 text-sm text-[#7752fe]
                    hover:text-[#6b4ae5] hover:bg-[#f1eeff] transition duration-200 ease-in-out">
                    <i class="fa-solid fa-file-invoice w-4 text-center"></i>
                    <span>Details</span>
                </button>
            
                {{-- Edit --}}
                <button
                    @click="
                        $dispatch('edit-item', {{ json_encode($item->only($editFields)) }});
                        open = false
                    "
                    class="cursor-pointer flex items-center gap-2 w-full px-4 py-2 text-sm text-yellow-500
                    hover:text-yellow-600 hover:bg-yellow-50 transition duration-200 ease-in-out">
                    <i class="fa-solid fa-pen w-4 text-center"></i>
                    <span>Edit</span>
                </button>
            
                {{-- Delete --}}
                <button
                    @click="
                        $dispatch('delete-item', '{{ route($deleteRoute, $item->id) }}');
                        open = false
                    "
                    class="cursor-pointer flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600
                    hover:text-red-700 hover:bg-red-50 transition duration-200 ease-in-out">
                    <i class="fa-solid fa-trash w-4 text-center"></i>
                    <span>Delete</span>
                </button>
            @endif
        </div>
    </template>
</div>