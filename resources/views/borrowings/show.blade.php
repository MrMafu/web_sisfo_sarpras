@extends('layouts.app')

@section('title', 'Borrowing Details')
@section('page-title', 'Dashboard > Borrowings > Details')

@section('content')
    <div class="flex h-screen bg-gray-50">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.topbar')

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="container mx-auto p-6">
                    {{-- Status Banner --}}
                    <div class="mb-6 p-3 rounded-lg flex items-center
                    @switch($borrowing->status)
                        @case('pending') bg-yellow-100 text-yellow-800 @break
                        @case('approved') bg-green-100 text-green-800 @break
                        @case('rejected') bg-red-100 text-red-800 @break
                        @case('returned') bg-blue-100 text-blue-800 @break
                    @endswitch">
                        <i class="fas fa-info-circle mr-3 text-lg"></i>
                        <h3 class="font-semibold">Status: {{ ucfirst($borrowing->status) }}</h3>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            {{-- Borrowing Overview Card --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-xl font-bold mb-4 flex items-center">
                                    <i class="fas fa-clipboard-list mr-2 text-[#7752fe]"></i>
                                    Borrowing Overview
                                </h2>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Item:</span>
                                        <span>{{ $borrowing->item->name }}</span>
                                    </div>

                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Borrower name:</span>
                                        <span>{{ $borrowing->user->username }}</span>
                                    </div>

                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Quantity:</span>
                                        <span>{{ $borrowing->quantity }}</span>
                                    </div>

                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Status:</span>
                                        <span class="capitalize">{{ $borrowing->status }}</span>
                                    </div>

                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Due Date:</span>
                                        <span>{{ \Carbon\Carbon::parse($borrowing->due)->format('M d, Y H:i') }}</span>
                                    </div>

                                    @if($borrowing->approved_at)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">Approved At:</span>
                                            <span>{{ \Carbon\Carbon::parse($borrowing->approved_at)->format('M d, Y H:i') }}</span>
                                        </div>
                                    @endif

                                    @if($borrowing->approver)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">Approved By:</span>
                                            <span>{{ $borrowing->approver->username }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-6 text-sm text-gray-500">
                                    <div>
                                        <span>Created at:</span>
                                        <span>{{ \Carbon\Carbon::parse($borrowing->created_at)->format('M d, Y H:i') }}</span>
                                    </div>
                                    <div>
                                        <span>Last Update at:</span>
                                        <span>{{ \Carbon\Carbon::parse($borrowing->updated_at)->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Borrower Information Card --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-xl font-bold mb-4 flex items-center">
                                    <i class="fas fa-user-tag mr-2 text-[#7752fe]"></i>
                                    Borrower Information
                                </h2>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Username:</span>
                                        <span>{{ $borrowing->user->username }}</span>
                                    </div>

                                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                        <span class="font-semibold">Role:</span>
                                        <span class="capitalize">{{ $borrowing->user->role }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-6">
                            {{-- Borrowed Units Card --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-xl font-bold mb-4 flex items-center">
                                    <i class="fas fa-barcode mr-2 text-[#7752fe]"></i>
                                    Borrowed Units
                                </h2>

                                @if ($borrowing->borrowingDetails->count() > 0)
                                    <div class="space-y-3">
                                        @foreach ($borrowing->borrowingDetails as $detail)
                                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded">
                                                <div class="flex items-center space-x-3">
                                                    <img src="{{ data_get($borrowing->item, 'image') }}" alt="Item Image" class="w-16 h-16 object-cover rounded">
                                                    <div>
                                                        <p class="font-semibold">SKU: {{ $detail->itemUnit->sku }}</p>
                                                        <p class="text-sm text-gray-600">
                                                            Status: <span class="capitalize">{{ $detail->itemUnit->status }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <i class="fas fa-box-open text-gray-500 ml-3"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-between p-4 bg-yellow-50 text-yellow-700 rounded-lg">
                                        <i class="fas fa-exclamation-circle mr-4 text-lg"></i>
                                        No units assigned yet. Units will be allocated upon approval.
                                    </div>
                                @endif
                            </div>

                            {{-- Approval Section --}}
                            @if ($borrowing->status === 'pending' && auth()->user()->role === 'admin')
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-xl font-bold mb-4 flex items-center">
                                        <i class="fas fa-clipboard-check mr-2 text-[#7752fe]"></i>
                                        Request Actions
                                    </h3>
                                    <form method="POST" action="{{ route('borrowings.update', $borrowing->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-4">
                                            <button type="submit" name="status" value="approved"
                                                class="cursor-pointer p-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-200 ease-in-out flex items-center justify-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Approve Request
                                            </button>
                                            <button type="submit" name="status" value="rejected"
                                                class="cursor-pointer p-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-200 ease-in-out flex items-center justify-center">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Reject Request
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            {{-- Return Information Card --}}
                            <div class="bg-white rounded-lg shadow p-6">
                                <h2 class="text-xl font-bold mb-4 flex items-center">
                                    <i class="fas fa-undo mr-2 text-[#7752fe]"></i>
                                    Return Status
                                </h2>

                                @if ($borrowing->returning)
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">Return Date:</span>
                                            <span>{{ \Carbon\Carbon::parse($borrowing->returning->returned_at)->format('M d, Y H:i') }}</span>
                                        </div>

                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">Status:</span>
                                            <span class="capitalize">{{ $borrowing->returning->status }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="p-4 bg-blue-50 text-blue-700 rounded-lg">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No return request has been registered yet
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection