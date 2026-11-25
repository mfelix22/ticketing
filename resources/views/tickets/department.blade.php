<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl leading-tight">
                {{ __('Department Tickets') }}
            </h2>
            <span class="text-sm text-gray-600">
                <i class="fas fa-building mr-1"></i>
                {{ Auth::user()->department->name ?? 'No Department' }}
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-64">
                            <label for="searchInput" class="block text-sm font-medium text-gray-700 mb-2">Search
                                Tickets</label>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Search by title, request number, requester, or recipient...">
                        </div>
                        <div class="flex-1 min-w-64">
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Filter by
                                Status</label>
                            <select name="status" id="statusFilter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->name }}"
                                        {{ request('status') == $status->name ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            @if (request('search') || request('status'))
                                <a href="{{ route('tickets.department') }}"
                                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="ticketsContainer">
                @include('tickets.partials.department-tickets-table')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const ticketsContainer = document.getElementById('ticketsContainer');

            const urlParams = new URLSearchParams(window.location.search);
            let currentPage = parseInt(urlParams.get('page') || 1);

            function fetchTickets() {
                const searchValue = searchInput.value;
                const statusValue = statusFilter.value;

                const params = new URLSearchParams({
                    page: currentPage,
                    ajax: '1'
                });

                if (searchValue) params.append('search', searchValue);
                if (statusValue) params.append('status', statusValue);

                fetch(`{{ route('tickets.department') }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        ticketsContainer.innerHTML = data.html;
                    })
                    .catch(error => console.error('Error fetching tickets:', error));
            }

            ticketsContainer.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (!link) return;
                try {
                    const url = new URL(link.href);
                    if (!url.searchParams.has('page')) return;
                    e.preventDefault();
                    currentPage = parseInt(url.searchParams.get('page')) || 1;
                    fetchTickets();
                } catch (err) {}
            });

            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentPage = 1;
                    fetchTickets();
                }, 1000);
            });

            statusFilter.addEventListener('change', function() {
                currentPage = 1;
                fetchTickets();
            });
        });
    </script>
</x-app-layout>
