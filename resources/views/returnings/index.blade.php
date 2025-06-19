@extends('layouts.app')

@section('title', 'Returnings')
@section('page-title', 'Dashboard > Returnings')

@section('content')
    <div class="flex h-screen bg-gray-50">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.topbar')

            <main class="flex flex-1 flex-col p-6">
                {{-- Header --}}
                <div class="pb-6 space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center
                        gap-4 mb-6">
                        <h2 class="text-2xl font-bold">Returnings</h2>
                        <div class="space-x-2">
                            <a href="{{ route('returnings.export', request()->query()) }}"
                                class="cursor-pointer inline-flex items-center bg-[#7752fe]
                                hover:bg-[#6b4ae5] text-white font-semibold px-5 py-2 rounded-lg shadow
                                transition duration-200 ease-in-out">
                                <i class="fa-solid fa-file-export mr-2"></i>
                                Export Excel
                            </a>
                        </div>
                    </div>

                    {{-- Status Flash --}}
                    @if (session('status'))
                        @include('components.alert')
                    @endif

                    {{-- Search & Sort & Filter --}}
                    @include('components.search-sort', [
                        'action'      => route('borrowings.index'),
                        'searchName'  => 'item',
                        'sortOptions' => [
                            'id'                => 'ID',
                            'borrowing_id'      => 'Item',
                            'returned_quantity' => 'Qty Returned',
                            'status'            => 'Status',
                            'created_at'        => 'Created',
                            'updated_at'        => 'Updated',
                        ],
                        'exclude' => ['status'],
                        'filters' => view('components.filters.dropdown', [
                            'name'    => 'status',
                            'label'   => 'Status',
                            'options' => [
                                'pending'  => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected'
                            ]
                        ])
                    ])
                </div>

                {{-- Table --}}
                <div class="flex-1 flex flex-col">
                    @php
                        $rowCount = $returnings->count();
                    @endphp
                    <div id="table-container" class="flex-1 overflow-auto
                        {{ $rowCount > 4 ? 'max-h-[15rem]' : ''}}">
                        <x-table
                            :items="$returnings"
                            :headers="[
                                'id'                      => 'ID',
                                'borrowing.item.name'     => 'Item',
                                'borrowing.user.username' => 'User',
                                'returned_quantity'       => 'Qty Returned',
                                'status'                  => 'Status',
                                'created_at'              => 'Created',
                                'updated_at'              => 'Updated',
                            ]"
                            :sortField="$sort"
                            :sortDirection="$direction"
                            emptyMessage="No returnings found."
                            :actions="true"
                            :actionProps="['showRoute' => 'returnings.show']"
                        />
                    </div>

                    {{-- Pagination --}}
                    @if ($returnings->hasPages())
                        <div class="flex justify-center mt-6 pagination">
                            {{ $returnings->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableContainer = document.getElementById('table-container');
            const searchForm = document.querySelector('#search-sort-form');
            const searchInput = document.querySelector('input[name="search"]');
            let abortController = null, searchTimeout = null;

            const fetchData = async (params) => {
                abortController?.abort();
                abortController = new AbortController();

                try {
                    const response = await fetch(`${location.pathname}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        signal: abortController.signal
                    });

                    tableContainer.innerHTML = await response.text();
                } catch (e) {
                    if (e.name !== 'AbortError') console.error('Error:', e);
                }
            };

            const handleFormSubmit = (e) => {
                e?.preventDefault();
                fetchData(new URLSearchParams(new FormData(searchForm)));
            };

            searchForm.addEventListener('submit', handleFormSubmit);
            searchForm.addEventListener('change', handleFormSubmit);

            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(handleFormSubmit, 200);
            });

            tableContainer.addEventListener('click', async (e) => {
                const link = e.target.closest('a');
                if (link?.closest('.pagination')) {
                    e.preventDefault();
                    await fetchData(new URL(link.href).searchParams);
                }
            });
        });
    </script>
@endsection