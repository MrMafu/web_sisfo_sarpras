<header x-data="{ open: false }"
    class="flex items-center justify-between bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-30">
    <div class="text-xl font-semibold">
        @yield('page-title')
    </div>
    
    <div class="relative" @click.outside="open = false">
        <button @click="open = !open" class="cursor-pointer flex items-center space-x-2 focus:outline-none">
            <span>Hi, <span class="font-medium text-[#7752fe]">{{ auth()->user()->username }}</span></span>
            <i class="fa-solid fa-chevron-down text-sm text-gray-700"
            :class="{ 'transform rotate-180': open }"></i>
        </button>
        <div x-show="open" x-transition
            class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow z-50">
            <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 space-x-2
            transition duration-100 ease-in-out">
                <i class="fa-solid fa-circle-user mr-2"></i>
                Profile
            </a>
            <div class="border-t border-gray-200 my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                class="cursor-pointer w-full flex items-center px-4 py-2 text-red-600 hover:text-red-700
                hover:bg-red-50 space-x-2 focus:outline-none transition duration-100 ease-in-out">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
</header>