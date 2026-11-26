<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl leading-tight">
                {{ __('My Tickets') }}
            </h2>
            <a href="{{ route('tickets.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Ticket
            </a>
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
                                placeholder="Search by title, request number, or request details...">
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
                                <a href="{{ route('tickets.index') }}"
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
                @if ($tickets->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        ID</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Title</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Customer / Recipient</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Department</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Priority</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Due Date</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Created</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($tickets as $ticket)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $ticket->request_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <a href="{{ route('tickets.show', $ticket) }}"
                                                    class="text-blue-600 hover:text-blue-900 font-medium">
                                                    {{ $ticket->title }}
                                                </a>
                                                @if ($ticket->attachments_count > 0)
                                                    <svg class="w-4 h-4 ml-2 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                    </svg>
                                                @endif
                                                @if (
                                                    $ticket->due_date &&
                                                        $ticket->schedule_date &&
                                                        $ticket->schedule_date > $ticket->due_date &&
                                                        !in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-bold rounded bg-red-100 text-red-800 animate-pulse">
                                                        🔥 OVERDUE
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            @if (is_array($ticket->detail_request) && isset($ticket->detail_request['recipient_name']))
                                                <div class="flex items-center">
                                                    <i class="fas fa-user text-gray-400 mr-2"></i>
                                                    <span>{{ $ticket->detail_request['recipient_name'] }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $ticket->department->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $ticket->priority->name === 'Urgent'
                                                    ? 'bg-red-100 text-red-800'
                                                    : ($ticket->priority->name === 'High'
                                                        ? 'bg-orange-100 text-orange-800'
                                                        : ($ticket->priority->name === 'Medium'
                                                            ? 'bg-yellow-100 text-yellow-800'
                                                            : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $ticket->priority->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                {{ $ticket->status->name === 'Open'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : ($ticket->status->name === 'In Progress'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : ($ticket->status->name === 'Completed'
                                                            ? 'bg-green-100 text-green-800'
                                                            : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $ticket->status->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if (in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                                <span class="text-success text-xs d-flex align-items-center">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    <span>Ticket finished</span>
                                                </span>
                                            @elseif ($ticket->due_date)
                                                @php
                                                    // Check if schedule_date exceeds due_date (late)
                                                    $isOverdue =
                                                        $ticket->schedule_date &&
                                                        $ticket->schedule_date > $ticket->due_date;
                                                    $hoursUntilDue = now()->diffInHours($ticket->due_date, false);
                                                    $isDueSoon =
                                                        !$isOverdue && $hoursUntilDue > 0 && $hoursUntilDue <= 24;
                                                @endphp
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-medium {{ $isOverdue ? 'text-red-600' : ($isDueSoon ? 'text-orange-600' : 'text-gray-900') }}">
                                                        {{ $ticket->due_date->format('M d, Y') }}
                                                    </span>
                                                    <span
                                                        class="text-xs {{ $isOverdue ? 'text-red-500 font-semibold' : ($isDueSoon ? 'text-orange-500' : 'text-gray-500') }}">
                                                        @if ($isOverdue)
                                                            Scheduled beyond Deadline! 🔥
                                                        @elseif ($isDueSoon)
                                                            @php
                                                                $hours = floor($hoursUntilDue);
                                                                $minutes = round(($hoursUntilDue - $hours) * 60);
                                                            @endphp
                                                            Due in {{ $hours }}h {{ $minutes }}m ⏰
                                                        @else
                                                            {{ $ticket->due_date->diffForHumans() }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">Not set</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $ticket->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-2">
                                                <!-- View Button -->
                                                <a href="{{ route('tickets.show', $ticket) }}"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View
                                                </a>

                                                @php
                                                    $statusName = $ticket->status->name ?? 'Open';
                                                    $isEditable = $statusName === 'Open';
                                                @endphp

                                                <!-- Edit Button (only for Open tickets) -->
                                                @if ($isEditable)
                                                    <a href="{{ route('tickets.edit', $ticket) }}"
                                                        class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md transition-colors duration-200"
                                                        style="background-color: #f97316; hover:background-color: #ea580c;">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Edit
                                                    </a>
                                                @endif

                                                <!-- Duplicate Button -->
                                                <form action="{{ route('tickets.duplicate', $ticket) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md transition-colors duration-200"
                                                        style="background-color: #6366f1;">
                                                        <i class="fas fa-copy mr-1"></i>
                                                        Duplicate
                                                    </button>
                                                </form>

                                                <!-- Cancel Request Button (only for non-finished tickets) -->
                                                @if (!in_array($statusName, ['Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User', 'Pending Cancellation']))
                                                    <button type="button"
                                                        onclick="openCancelModal({{ $ticket->id }})"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors duration-200">
                                                        <i class="fas fa-ban mr-1"></i>
                                                        Cancel
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $tickets->withQueryString()->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
                        <p class="mt-1 text-sm text-gray-600">Get started by creating a new ticket.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('tickets.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Create Your First Ticket
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const ticketsContainer = document.getElementById('ticketsContainer');

            // Initialize currentPage from URL parameter
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

                fetch(`{{ route('tickets.index') }}?${params.toString()}`, {
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

            // Event delegation: catch pagination link clicks inside the container
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

            // Debounced search - 1 second delay
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentPage = 1;
                    fetchTickets();
                }, 1000);
            });

            // Instant filter on status change
            statusFilter.addEventListener('change', function() {
                currentPage = 1;
                fetchTickets();
            });

            // Initial load
            fetchTickets();
        });

        // Cancel Modal Functions
        function openCancelModal(ticketId) {
            const modal = document.getElementById('cancelModal');
            const form = document.getElementById('cancelForm');
            const textarea = document.getElementById('cancellation_reason');

            // Store ticket ID in form data attribute for later use
            form.dataset.ticketId = ticketId;

            // Base URL aware of subdirectory deployments (e.g., /ticketing)
            const APP_BASE_URL = "{{ rtrim(url('/'), '/') }}";

            form.action = APP_BASE_URL + '/tickets/' + ticketId + '/cancel';

            // Clear previous input
            textarea.value = '';

            // Show modal (Bootstrap 4/5 compatible)
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');

            // Create backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'cancelModalBackdrop';
            document.body.appendChild(backdrop);

            // Focus on textarea
            setTimeout(() => textarea.focus(), 100);
        }

        function submitCancelForm(event) {
            event.preventDefault();
            const form = document.getElementById('cancelForm');
            const ticketId = form.dataset.ticketId;
            const reason = document.getElementById('cancellation_reason').value;

            if (!reason || reason.length < 10) {
                alert('Please provide a cancellation reason (minimum 10 characters).');
                return false;
            }

            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.content ||
                document.querySelector('input[name="_token"]')?.value;

            if (!token) {
                console.error('CSRF token not found');
                alert('Security token not found. Please refresh the page and try again.');
                return false;
            }

            // Submit form
            form.submit();
            return false;
        }

        function closeCancelModal() {
            const modal = document.getElementById('cancelModal');
            const backdrop = document.getElementById('cancelModalBackdrop');

            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');

            if (backdrop) {
                backdrop.remove();
            }
        }

        // Close modal on outside click
        document.getElementById('cancelModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCancelModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCancelModal();
            }
        });
    </script>

    <!-- Cancel Reason Modal -->
    <div id="cancelModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Request Ticket Cancellation
                    </h5>
                    <button type="button" class="close text-white" onclick="closeCancelModal()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cancelForm" method="POST" action="" onsubmit="submitCancelForm(event)">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Note:</strong> Your cancellation request will be sent to the PIC for approval.
                        </div>
                        <div class="form-group">
                            <label for="cancellation_reason">
                                <i class="fas fa-comment-dots mr-1"></i>
                                Reason for Cancellation <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="cancellation_reason" id="cancellation_reason" rows="4" required
                                minlength="10" maxlength="1000"
                                placeholder="Please explain why you want to cancel this ticket (minimum 10 characters)..."></textarea>
                            <small class="form-text text-muted">
                                Provide a clear reason so the PIC can understand your request.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">
                            <i class="fas fa-times mr-1"></i>Close
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-paper-plane mr-1"></i>Submit Cancellation Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
