@extends('layouts.app')

@section('title', 'Item Units')
@section('page-title', 'Dashboard > Item Units')

@section('content')
    <div x-data="{
        {{-- State management --}}
        modals: {
            view   : { open: false, item: null },
            create : { open: {{ $errors->create->isNotEmpty() || request()->has('create') ? 'true' : 'false' }} },
            edit   : { open: {{ $errors->edit->isNotEmpty() ? 'true' : 'false' }}, item: null },
            delete : { open: false, url: '' }
        },
        {{-- Date formatting --}}
        formatDateTime(dateString) {
            const date = new Date(dateString);
            const options = { 
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            return date.toLocaleDateString(undefined, options);
        },
        handleViewItem(data) {
            this.modals.view = { open: true, item: data };
        },
        handleEditItem(data) {
            this.modals.edit = { open: true, item: data };
        },
        handleDeleteItem(url) {
            this.modals.delete = { open: true, url: url };
        }
    }"
        @view-item.window="handleViewItem($event.detail)"
        @edit-item.window="handleEditItem($event.detail)"
        @delete-item.window="handleDeleteItem($event.detail)"
        class="flex h-screen bg-gray-50">
        @include('components.sidebar')

        <div class="flex-1 flex flex-col overflow-hidden">
            @include('components.topbar')

            <main class="flex flex-1 flex-col p-6">
                {{-- Header --}}
                <div class="pb-6 space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center
                        gap-4 mb-6">
                        <h2 class="text-2xl font-bold">Items</h2>
                        <div class="space-x-2">
                            <button @click="modals.create.open = true"
                                class="cursor-pointer inline-flex items-center bg-[#7752fe]
                                hover:bg-[#6b4ae5] text-white font-semibold px-5 py-2 rounded-lg shadow
                                transition duration-200 ease-in-out">
                                <i class="fa-solid fa-box mr-2"></i>
                                New Item Unit
                            </button>
                        </div>
                    </div>

                    {{-- Status Flash --}}
                    @if (session('status'))
                        @include('components.alert')
                    @endif

                    {{-- Search & Sort & Filter --}}
                    @include('components.search-sort', [
                        'action'      => route('item_units.index'),
                        'searchName'  => 'sku',
                        'sortOptions' => [
                            'id'         => 'ID',
                            'sku'        => 'SKU',
                            'status'     => 'Status',
                            'item_id'    => 'Item',
                            'created_at' => 'Created',
                            'updated_at' => 'Updated',
                        ],
                        'exclude' => ['status'],
                        'filters' => view('components.filters.dropdown', [
                            'name'    => 'status',
                            'label'   => 'Status',
                            'options' => [
                                'available' => 'Available',
                                'borrowed'  => 'Borrowed',
                                'unknown'   => 'Unknown'
                            ]
                        ])
                    ])
                </div>

                {{-- Table --}}
                <div class="flex-1 flex flex-col">
                    @php
                        $rowCount = $itemUnits->count();
                    @endphp
                    <div id="table-container" class="flex-1 overflow-auto
                        {{ $rowCount > 5 ? 'max-h-[15rem]' : ''}}">
                        <x-table
                            :items="$itemUnits"
                            :headers="[
                                'id'         => 'ID',
                                'sku'        => 'SKU',
                                'status'     => 'Status',
                                'item.name'  => 'Item',
                                'created_at' => 'Created',
                                'updated_at' => 'Updated',
                            ]"
                            :sortField="$sort"
                            :sortDirection="$direction"
                            emptyMessage="No item units found."
                            :actions="true"
                            :actionProps="[
                                'viewFields'  => [
                                    'id',
                                    'sku',
                                    'status',
                                    'item.name',
                                    'created_at',
                                    'updated_at'
                                ],
                                'editFields'  => ['id', 'sku', 'status', 'item_id'],
                                'deleteRoute' => 'item_units.destroy',
                            ]"
                        />
                    </div>

                    {{-- Pagination --}}
                    @if ($itemUnits->hasPages())
                        <div class="flex justify-center mt-6 pagination">
                            {{ $itemUnits->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </main>

            {{-- View Details Modal --}}
            <x-modals.view
                title="Item Units Details"
                :fields="[
                    'sku'        => 'SKU',
                    'status'     => 'Status',
                    'item.name'  => 'Item',
                    'created_at' => 'Created',
                    'updated_at' => 'Updated',
                ]"
                target="modals.view"
            />

            {{-- Create Modal --}}
            <x-modals.form
                title="Create New Item Unit"
                method="POST"
                :action="route('item_units.store')"
                :fields="[
                    'item_id' => [
                        'label'    => 'Item',
                        'type'     => 'select',
                        'options'  => \App\Models\Item::all()->pluck('name', 'id')->toArray(),
                        'required' => true
                    ],
                    'sku' => [
                        'label'    => 'Item SKU',
                        'required' => true
                    ]
                ]"
                errorBag="create"
                target="modals.create"
            />

            {{-- Edit Modal --}}
            <x-modals.form
                title="Edit Item Unit"
                method="PUT"
                :action="route('item_units.update', 'REPLACE')"
                :fields="[
                    'item_id' => [
                        'label'    => 'Item',
                        'type'     => 'select',
                        'options'  => \App\Models\Item::all()->pluck('name', 'id')->toArray(),
                        'required' => true
                    ],
                    'sku' => [
                        'label'    => 'Item SKU',
                        'required' => true
                    ]
                ]"
                errorBag="edit"
                target="modals.edit"
                submit-text="Update"
            />

            {{-- Delete Confirmation Dialog Modal --}}
            <x-modals.delete title="Confirm Item Unit Deletion" target="modals.delete">
                <x-slot name="actions">
                    <form x-bind:action="modals.delete.url" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700
                            transition duration-200 ease-in-out">
                            Yes, delete
                        </button>
                    </form>
                </x-slot>
                Are you sure you want to delete this item unit? This action cannot be undone.
            </x-modals.delete>
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

            // Event handlers
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