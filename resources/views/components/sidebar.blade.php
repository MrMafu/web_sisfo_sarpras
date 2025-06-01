<aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 sticky top-0 left-0 h-screen z-20">
    <div class="p-6 flex items-center space-x-2">
        <i class="fa-solid fa-warehouse text-[#7752fe] text-lg"></i>
        <span class="text-lg font-bold text-gray-800">SISFO SARPRAS</span>
    </div>

    <nav class="px-4 space-y-2">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('dashboard') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center text-lg mr-2">
                <i class="fa-solid fa-cubes"></i>
            </span>
            Dashboard
        </a>

        {{-- Users --}}
        <a href="{{ route('users.index') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('users.index') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center text-lg mr-2">
                <i class="fa-solid fa-user"></i>
            </span>
            Users
        </a>

        {{-- Categories --}}
        <a href="{{ route('categories.index') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('categories.index') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center text-lg mr-2">
                <i class="fa-solid fa-shapes"></i>
            </span>
            Categories
        </a>

        {{-- Items --}}
        <a href="{{ route('items.index') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('items.index') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center mr-2">
                <x-fluentui-box-multiple-24 />
            </span>
            Items
        </a>

        {{-- Item Units --}}
        <a href="{{ route('item_units.index') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('item_units.index') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center mr-2">
                <x-fluentui-box-24 />
            </span>
            Item Units
        </a>

        {{-- Borrowings --}}
        <a href="{{ route('borrowings.index') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('borrowings.index') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center mr-2">
                <x-lucide-square-arrow-out-up-right />
            </span>
            Borrowings
        </a>

        {{-- Returnings --}}
        <a href="{{ route('returnings.index') }}"
            class="flex items-center px-4 py-2 rounded hover:bg-[#f1eeff] transition duration-100 ease-in-out
            {{ request()->routeIs('returnings.index') ? 'bg-[#f1eeff] text-[#7752fe] font-medium' : 'text-gray-700' }}">
            <span class="w-6 text-center mr-2">
                <x-lucide-square-arrow-out-down-left />
            </span>
            Returnings
        </a>
    </nav>
</aside>