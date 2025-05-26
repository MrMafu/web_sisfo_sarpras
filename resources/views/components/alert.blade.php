<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition
    class="fixed top-6 right-6 flex items-center space-x-3 rounded-md bg-green-50
    border border-green-200 px-4 py-3 text-green-800 shadow-lg z-50">
    <i class="fa-solid fa-circle-check text-lg"></i>
    <span>{{ session('status') }}</span>
    <button @click="show = false"
        class="ml-4 text-green-600 hover:text-green-800 focus:outline-none transition
        duration-200 ease-in-out">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>