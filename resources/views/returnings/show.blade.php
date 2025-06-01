@extends('layouts.app')

@section('title', 'Returning Details')
@section('page-title', 'Dashboard > Returnings > Details')

@section('content')
    <div class="flex h-screen bg-gray-50">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.topbar')

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="container mx-auto p-6">
                    {{-- Status Banner --}}
                    <div class="mb-6 p-4 rounded-lg flex items-center font-medium
                        @switch ($returning->status)
                            @case ('pending') bg-yellow-100 text-yellow-800 @break
                            @case ('approved') bg-green-100 text-green-800 @break
                            @case ('rejected') bg-red-100 text-red-800 @break
                        @endswitch
                    ">
                        <i class="fa-solid fa-circle-info mr-3 text-lg"></i>
                        <span>Status: {{ ucfirst($returning->status) }}</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            {{-- Returning Overview Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fa-solid fa-clipboard-list mr-2 text-[#7752fe]"></i>
                                    <h2>Returning Overview</h2>
                                </div>

                                <div class="space-y-4">
                                    @foreach ([
                                        ['ID', $returning->id],
                                        ['Returned Quantity', $returning->returned_quantity],
                                        ['Status', $returning->status]
                                    ] as [$label, $value])
                                        <div class="flex justify-between items-center p-3 bg-gray-50
                                            rounded">
                                            <span class="font-semibold">{{ $label }}:</span>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endforeach
                                    
                                    @if ($returning->returned_at)
                                        <div class="flex justify-between items-center p-3 bg-gray-50
                                            rounded">
                                            <span class="font-semibold">Returned At:</span>
                                            <span>
                                                {{ \Carbon\Carbon::parse($returning->returned_at)
                                                ->format('M d, Y H:i') }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if ($returning->handler)
                                        <div class="flex justify-between items-center p-3 bg-gray-50
                                            rounded">
                                            <span class="font-semibold">Handled By:</span>
                                            <span>{{ $returning->handler->username }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-gray-500 space-y-1">
                                    <div>
                                        Created at: {{ \Carbon\Carbon::parse($returning->created_at)
                                        ->format('M d, Y H:i') }}
                                    </div>
                                    <div>
                                        Last Update: {{ \Carbon\Carbon::parse($returning->updated_at)
                                        ->format('M d, Y H:i') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Borrower Information Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fa-solid fa-id-card mr-2 text-[#7752fe]"></i>
                                    <h2>Borrower Information</h2>
                                </div>
                                
                                <div class="space-y-4">
                                    @foreach ([
                                        ['Username', $returning->borrowing->user->username],
                                        ['Role', ucfirst($returning->borrowing->user->role)],
                                    ] as [$label, $value])
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">{{ $label }}:</span>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-6">
                            {{-- Assigned Borrowing Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fas fa-book mr-2 text-[#7752fe]"></i>
                                    <h2>Assigned Borrowing</h2>
                                </div>
                                
                                <div class="space-y-4">
                                    @foreach ([
                                        ['Borrowing ID', $returning->borrowing->id],
                                        ['Item', $returning->borrowing->item->name],
                                        ['Quantity', $returning->borrowing->quantity],
                                        ['Due Date', \Carbon\Carbon::parse($returning->borrowing->due)
                                            ->format('M d, Y H:i')],
                                        ['Status', ucfirst($returning->borrowing->status)]
                                    ] as [$label, $value])
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                            <span class="font-semibold">{{ $label }}:</span>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Returned Units Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fa-solid fa-circle-check mr-2 text-[#7752fe]"></i>
                                    <h2>Returned Units</h2>
                                </div>
                                
                                @if ($returnedUnits->count() > 0)
                                    <div class="space-y-3 
                                        {{ $returnedUnits->count() > 3 ?
                                        'max-h-64 overflow-y-auto' : '' }}">
                                        @foreach ($returnedUnits as $detail)
                                            <div class="flex items-center justify-between p-3 border
                                                border-gray-200 rounded">
                                                <div class="flex items-center space-x-3">
                                                    <img src="{{ $returning->borrowing->item->image }}" 
                                                         alt="Item Image" 
                                                         class="w-16 h-16 object-cover rounded">
                                                    <div>
                                                        <p class="font-semibold">
                                                            SKU: {{ $detail->itemUnit->sku }}
                                                        </p>
                                                        <p class="text-sm text-gray-600">
                                                            Status:
                                                            <span class="capitalize">
                                                                {{ $detail->itemUnit->status }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <i class="fas fa-check text-green-500"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg flex
                                        items-center">
                                        <i class="fas fa-exclamation-circle mr-3"></i>
                                        <span>No units returned yet</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Not Returned Units Card --}}
                            @if ($notReturnedUnits->count() > 0)
                                <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                    <div class="flex items-center text-xl font-bold">
                                        <i class="fas fa-times-circle mr-2 text-[#7752fe]"></i>
                                        <h2>Not Returned Units</h2>
                                    </div>
                                    
                                    <div class="space-y-3 
                                        {{ $notReturnedUnits->count() > 3 ?
                                        'max-h-64 overflow-y-auto' : '' }}">
                                        @foreach ($notReturnedUnits as $detail)
                                            <div class="flex items-center justify-between p-3 border
                                                border-gray-200 rounded">
                                                <div class="flex items-center space-x-3">
                                                    <img src="{{ $returning->borrowing->item->image }}" 
                                                        alt="Item Image" 
                                                        class="w-16 h-16 object-cover rounded">
                                                    <div>
                                                        <p class="font-semibold">
                                                            SKU: {{ $detail->itemUnit->sku }}
                                                        </p>
                                                        <p class="text-sm text-gray-600">
                                                            Status:
                                                            <span class="capitalize">
                                                                {{ $detail->itemUnit->status }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <i class="fas fa-times text-red-500"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Request Actions --}}
                            @if ($returning->status === 'pending' && auth()->user()->role === 'admin')
                                <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                    <div class="flex items-center text-xl font-bold">
                                        <i class="fas fa-clipboard-check mr-2 text-[#7752fe]"></i>
                                        <h3>Request Actions</h3>
                                    </div>
                                    
                                    <form
                                        method="POST"
                                        action="{{ route('returnings.update', $returning->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-4">
                                            <button type="submit" name="status" value="approved"
                                                class="cursor-pointer p-3 bg-green-500 text-white
                                                rounded-lg hover:bg-green-600 transition duration-200
                                                ease-in-out flex items-center justify-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Approve
                                            </button>
                                            
                                            <button type="submit" name="status" value="rejected"
                                                class="cursor-pointer p-3 bg-red-500 text-white rounded-lg
                                                hover:bg-red-600 transition duration-200 ease-in-out flex
                                                items-center justify-center">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection