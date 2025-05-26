@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="flex min-h-screen bg-[#7752fe]">
        {{-- Login Card --}}
        <div class="flex-1 flex items-center justify-start pl-25">
            <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
                <h1 class="text-3xl font-bold text-center text-[#7752fe] mb-6">Log In</h1>

                @if ($errors->has('login'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4">
                        {{ $errors->first('login') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="username" class="block text-sm text-gray-600 font-medium mb-1">Username</label>
                        <div class="relative transform transition duration-200 ease-in-out focus-within:scale-101">
                            <span
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-auto
                                transform transition duration-200 ease-in-out focus-within:scale-101">
                                <i class="fa-solid fa-user text-gray-400"></i>
                            </span>

                            <input name="username" type="text" value="{{ old('username') }}" required
                                class="block w-full pl-10 pr-4 py-2 border @error('username') border-red-500
                                @else border-gray-400 @enderror rounded-md focus:outline-none
                                focus:border-[#7752fe] transform transition duration-200 ease-in-out">
                        </div>
                        @error('username')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm text-gray-600 font-medium mb-1">Password</label>
                        <div class="relative transform transition duration-200 ease-in-out focus-within:scale-101">
                            <span
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-auto
                                transform transition duration-200 ease-in-out focus-within:scale-101">
                                <i class="fa-solid fa-lock text-gray-400"></i>
                            </span>

                            <input name="password" type="password" required
                                class="block w-full pl-10 pr-4 py-2 border @error('password') border-red-500
                                @else border-gray-400 @enderror rounded-md focus:outline-none
                                focus:border-[#7752fe] transform transition duration-200 ease-in-out">
                        </div>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="cursor-pointer w-full py-2 text-white font-medium border border-[#7752fe]
                        bg-[#7752fe] rounded-md hover:scale-101 hover:bg-transparent hover:text-[#7752fe]
                        transition duration-200 ease-in-out">
                        Log In
                    </button>
                </form>
            </div>
        </div>

        {{-- Header Section --}}
        <div class="flex-1 flex items-center justify-center pr-25">
            <div class="text-white text-center space-y-2">
                <h2 class="text-4xl font-bold">SISFO SARPRAS</h2>
                <p class="text-lg">Access and start managing.</p>
            </div>
        </div>
    </div>
@endsection