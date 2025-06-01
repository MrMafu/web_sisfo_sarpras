@extends('layouts.app')

@section('title', 'SISFO SARPRAS')
@section('page-title', 'Dashboard')

@section('content')
    @php
        use Carbon\Carbon;
        $hour = Carbon::now('Asia/Jakarta')->hour;

        if ($hour >= 18 || $hour < 6) {
            $greeting = 'Good night';
        } elseif ($hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour < 15) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }
    @endphp

    <div class="flex h-screen bg-gray-50">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col">
            @include('components.topbar')

            <main class="flex-1 overflow-auto p-6">
                <h1 class="text-2xl font-semibold mb-6">
                    {{ $greeting }}, <span class="text-[#7752fe]">{{ auth()->user()->username }}</span>!
                </h1>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    {{-- Total Users --}}
                    <div class="bg-white rounded-lg shadow p-5 flex items-center">
                        <div class="w-16 h-16 flex items-center justify-center bg-[#e4dcff] rounded-full">
                            <i class="fa-solid fa-user text-[#7752fe] text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Users</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $totalUsers }}</p>
                        </div>
                    </div>

                    {{-- Total Categories --}}
                    <div class="bg-white rounded-lg shadow p-5 flex items-center">
                        <div class="w-16 h-16 flex items-center justify-center bg-[#e4dcff] rounded-full">
                            <i class="fa-solid fa-shapes text-[#7752fe] text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Categories</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $totalCategories }}</p>
                        </div>
                    </div>

                    {{-- Total Items --}}
                    <div class="bg-white rounded-lg shadow p-5 flex items-center">
                        <div class="w-16 h-16 flex items-center justify-center bg-[#e4dcff] rounded-full">
                            {{-- <i class="fa-solid fa-boxes-stacked text-[#7752fe] text-2xl"></i> --}}
                            <x-fluentui-box-multiple-24 class="text-[#7752fe] w-8 h-8" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Items</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $totalItems }}</p>
                        </div>
                    </div>

                    {{-- Total Units --}}
                    <div class="bg-white rounded-lg shadow p-5 flex items-center">
                        <div class="w-16 h-16 flex items-center justify-center bg-[#e4dcff] rounded-full">
                            {{-- <i class="fa-solid fa-box text-[#7752fe] text-2xl"></i> --}}
                            <x-fluentui-box-24 class="text-[#7752fe] w-8 h-8" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Units</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $totalUnits }}</p>
                        </div>
                    </div>

                    {{-- Borrowings Pending --}}
                    <div class="bg-white rounded-lg shadow p-5 flex items-center">
                        <div class="w-16 h-16 flex items-center justify-center bg-[#e4dcff] rounded-full">
                            <i class="fa-solid fa-clock text-[#7752fe] text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Borrowings Pending</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $pendingBorrowings }}</p>
                        </div>
                    </div>

                    {{-- Returnings Pending --}}
                    <div class="bg-white rounded-lg shadow p-5 flex items-center">
                        <div class="w-16 h-16 flex items-center justify-center bg-[#e4dcff] rounded-full">
                            <i class="fa-solid fa-clock-rotate-left text-[#7752fe] text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Returnings Pending</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $pendingReturnings }}</p>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="bg-white rounded-lg shadow p-5 col-span-1 sm:col-span-2 lg:col-span-3">
                        <p class="text-sm text-gray-500 mb-3">Quick Actions</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <a href="{{ route('users.index', ['create' => 1]) }}"
                                class="flex items-center p-3 bg-[#f1eeff] hover:bg-[#e4dcff] rounded transition">
                                <i class="fa-solid fa-user-plus text-[#7752fe] text-lg mr-2"></i>
                                <span>Create User</span>
                            </a>
                            <a href="{{ route('categories.index', ['create' => 1]) }}"
                                class="flex items-center p-3 bg-[#f1eeff] hover:bg-[#e4dcff] rounded transition">
                                <i class="fa-solid fa-plus text-[#7752fe] text-lg mr-2"></i>
                                <span>Add Category</span>
                            </a>
                            <a href="{{ route('items.index', ['create' => 1]) }}"
                                class="flex items-center p-3 bg-[#f1eeff] hover:bg-[#e4dcff] rounded transition">
                                <i class="fa-solid fa-plus text-[#7752fe] text-lg mr-2"></i>
                                <span>Add Item</span>
                            </a>
                            <a href="{{ route('item_units.index', ['create' => 1]) }}"
                                class="flex items-center p-3 bg-[#f1eeff] hover:bg-[#e4dcff] rounded transition">
                                <i class="fa-solid fa-plus text-[#7752fe] text-lg mr-2"></i>
                                <span>Add Unit</span>
                            </a>
                            <a href="{{ route('borrowings.index') }}"
                                class="flex items-center p-3 bg-[#f1eeff] hover:bg-[#e4dcff] rounded transition">
                                <x-lucide-square-arrow-out-up-right class="w-5 h-5 text-[#7752fe] mr-2" />
                                <span>View Borrowings</span>
                            </a>
                            <a href="{{ route('returnings.index') }}"
                                class="flex items-center p-3 bg-[#f1eeff] hover:bg-[#e4dcff] rounded transition">
                                <x-lucide-square-arrow-out-down-left class="w-5 h-5 text-[#7752fe] mr-2" />
                                <span>View Returnings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection