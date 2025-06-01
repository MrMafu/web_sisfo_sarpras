<header x-data="{ open: false, profileOpen: false }"
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
            <button @click="profileOpen = true; open = false"
                class="cursor-pointer w-full flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100
                space-x-2 transition duration-100 ease-in-out">
                <i class="fa-solid fa-circle-user mr-2"></i>
                Profile
            </button>

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

    {{-- Profile Modal --}}
    <div
        x-show="profileOpen"
        x-cloak
        x-transition.opacity
        class="z-50 fixed inset-0 bg-black/50 flex items-center justify-center">
        <div
            @click.outside="profileOpen = false"
            x-show="profileOpen"
            x-transition
            class="bg-white rounded-lg shadow-lg w-full max-w-md overflow-hidden relative">
            {{-- Banner --}}
            <div class="h-24 bg-gradient-to-r from-[#7752fe] to-[#8e6cfe]"></div>
            
            {{-- Profile Picture --}}
            <div class="absolute top-12 left-6">
                <div class="bg-gray-200 border-4 border-white rounded-full w-20 h-20 flex items-center justify-center">
                    <span class="text-2xl font-bold text-gray-700">
                        {{ substr(auth()->user()->username, 0, 1) }}
                    </span>
                </div>
            </div>
            
            <div class="p-6">
                {{-- User Info --}}
                <div class="mt-6 mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">{{ auth()->user()->username }}</h2>
                    <div class="flex items-center mt-1">
                        <span class="bg-[#e4dcff] text-[#7752fe] text-xs px-2 py-1 rounded-full">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                    </div>
                </div>
                
                {{-- Account Details --}}
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-8 text-center mr-3 text-gray-500">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Account Created</p>
                            <p class="text-sm font-medium text-gray-700">
                                {{ auth()->user()->created_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-8 text-center mr-3 text-gray-500">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Last Updated</p>
                            <p class="text-sm font-medium text-gray-700">
                                {{ auth()->user()->updated_at->format('M d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="mt-8 flex justify-end">
                    <button @click="profileOpen = false"
                        class="cursor-pointer px-4 py-2 bg-[#7752fe] text-white rounded-md hover:bg-[#6b4ae5] transition
                        duration-200 ease-in-out">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>