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
                    <div class="mb-6 p-4 rounded-lg flex items-center font-medium
                        @switch ($borrowing->status)
                            @case ('pending') bg-yellow-100 text-yellow-800 @break
                            @case ('approved') bg-green-100 text-green-800 @break
                            @case ('rejected') bg-red-100 text-red-800 @break
                            @case ('overdue') bg-orange-100 text-orange-800 @break
                            @case ('returned') bg-blue-100 text-blue-800 @break
                        @endswitch
                    ">
                        <i class="fas fa-info-circle mr-3 text-lg"></i>
                        <span>Status: {{ ucfirst($borrowing->status) }}</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            {{-- Borrowing Overview Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fas fa-clipboard-list mr-2 text-[#7752fe]"></i>
                                    <h2>Borrowing Overview</h2>
                                </div>

                                <div class="space-y-4">
                                    @foreach ([
                                        ['Item', $borrowing->item->name],
                                        ['Borrower name', $borrowing->user->username],
                                        ['Quantity', $borrowing->quantity],
                                        ['Status', ucfirst($borrowing->status)],
                                        ['Due Date', \Carbon\Carbon::parse($borrowing->due)
                                            ->format('M d, Y H:i')],
                                    ] as [$label, $value])
                                        <div class="flex justify-between items-center p-3 bg-gray-50
                                            rounded">
                                            <span class="font-semibold">{{ $label }}:</span>
                                            <span>{{ $value }}</span>
                                        </div>
                                    @endforeach

                                    @if ($borrowing->approved_at)
                                        <div class="flex justify-between items-center p-3 bg-gray-50
                                            rounded">
                                            <span class="font-semibold">Approved At:</span>
                                            <span>
                                                {{ \Carbon\Carbon::parse($borrowing->approved_at)
                                                ->format('M d, Y H:i') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($borrowing->approver)
                                        <div class="flex justify-between items-center p-3 bg-gray-50
                                            rounded">
                                            <span class="font-semibold">Approved By:</span>
                                            <span>{{ $borrowing->approver->username }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-sm text-gray-500 space-y-1">
                                    <div>
                                        Created at: {{ \Carbon\Carbon::parse($borrowing->created_at)
                                        ->format('M d, Y H:i') }}
                                    </div>
                                    <div>
                                        Last Update: {{ \Carbon\Carbon::parse($borrowing->updated_at)
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
                                        ['Username', $borrowing->user->username],
                                        ['Role', ucfirst($borrowing->user->role)]
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
                            {{-- Borrowed Units Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fas fa-barcode mr-2 text-[#7752fe]"></i>
                                    <h2>Borrowed Units</h2>
                                </div>

                                @if ($borrowing->borrowingDetails->count())
                                    <div class="space-y-3
                                        {{ $borrowing->borrowingDetails->count() > 3 ?
                                        'max-h-64 overflow-y-auto' : '' }}">
                                        @foreach ($borrowing->borrowingDetails as $detail)
                                            <div class="flex items-center justify-between p-3 border
                                                border-gray-200 rounded">
                                                <div class="flex items-center space-x-3">
                                                    <img
                                                        src="{{ data_get($borrowing->item, 'image') }}"
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
                                                <i class="fas fa-box-open text-gray-500"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-between p-4 bg-yellow-50
                                        text-yellow-700 rounded-lg">
                                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                                        <span>
                                            No units assigned yet. Units will be allocated upon approval.
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Request Actions --}}
                            @if ($borrowing->status === 'pending' && auth()->user()->role === 'admin')
                                <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                    <div class="flex items-center text-xl font-bold">
                                        <i class="fas fa-clipboard-check mr-2 text-[#7752fe]"></i>
                                        <h3>Request Actions</h3>
                                    </div>
                                    <form
                                        method="POST"
                                        action="{{ route('borrowings.update', $borrowing->id) }}">
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

                            {{-- Return Status Card --}}
                            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                                <div class="flex items-center text-xl font-bold">
                                    <i class="fas fa-undo mr-2 text-[#7752fe]"></i>
                                    <h2>Return Status</h2>
                                </div>

                                @if ($borrowing->returning)
                                    <div class="space-y-4">
                                        @foreach ([
                                            ['Return Date', $borrowing->returning->returned_at ? 
                                                \Carbon\Carbon::parse($borrowing->returning->returned_at)
                                                ->format('M d, Y H:i') : '-'],
                                            ['Status', ucfirst($borrowing->returning->status)],
                                            ['Handled by', $borrowing->returning->handler->username ?? '-']
                                        ] as [$label, $value])
                                            <div class="flex justify-between items-center p-3 bg-gray-50
                                                rounded">
                                                <span class="font-semibold">{{ $label }}:</span>
                                                <span>{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 bg-blue-50 text-blue-700 rounded-lg flex items-center">
                                        <i class="fas fa-info-circle mr-3"></i>
                                        <span>No return request has been registered yet.</span>
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