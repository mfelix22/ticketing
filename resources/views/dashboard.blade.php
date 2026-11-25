<x-app-layout>
    <x-slot name="header">
        <h1 class="m-0 text-gray-800 text-2xl font-semibold">Dashboard</h1>
    </x-slot>

    @php
        // Overdue = schedule_date > due_date (scheduled beyond SLA deadline)
        $overdueTickets = Auth::user()
            ->requestedTickets()
            ->whereNotNull('schedule_date')
            ->whereNotNull('due_date')
            ->whereRaw('schedule_date > due_date')
            ->whereHas('status', function ($q) {
                $q->whereNotIn('name', ['Completed', 'Closed', 'Cancelled']);
            })
            ->with(['status', 'priority'])
            ->get();

        $assignedOverdueTickets = collect();
        if (Auth::user()->is_pic || in_array(Auth::user()->status, ['Approver', 'Both'])) {
            $user = Auth::user();
            $query = App\Models\Ticket::whereNotNull('schedule_date')
                ->whereNotNull('due_date')
                ->whereRaw('schedule_date > due_date')
                ->whereHas('status', function ($q) {
                    $q->whereNotIn('name', ['Completed', 'Closed', 'Cancelled']);
                })
                ->where(function ($q) use ($user) {
                    // Only show tickets assigned/handled by current user
                    $q->where('assigned_to', $user->id)
                        ->orWhere('handling_user_id', $user->id)
                        ->orWhere('receipt_user_id', $user->id);

                    // If user is also a PIC in a department, include unassigned tickets from that dept
                    if ($user->is_pic && $user->department_id) {
                        $q->orWhere(function ($q2) use ($user) {
                            $q2->where('department_id', $user->department_id)
                                ->whereNull('assigned_to')
                                ->whereNull('handling_user_id');
                        });
                    }
                });

            $assignedOverdueTickets = $query->with(['status', 'priority'])->get();
        }
    @endphp

    <div class="w-full min-h-screen bg-gray-100 p-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden p-6">

            <!-- Overdue Alert Banner -->
            @if ($overdueTickets->count() > 0 || $assignedOverdueTickets->count() > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-500 animate-pulse" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-red-800">
                                🔥 Overdue Tickets Alert
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @if ($overdueTickets->count() > 0)
                                        <li>
                                            <strong>{{ $overdueTickets->count() }}</strong> of your tickets are overdue
                                            <a href="{{ route('tickets.index') }}"
                                                class="underline font-semibold hover:text-red-900">View Tickets →</a>
                                        </li>
                                    @endif
                                    @if ($assignedOverdueTickets->count() > 0)
                                        <li>
                                            <strong>{{ $assignedOverdueTickets->count() }}</strong> assigned tickets are
                                            overdue
                                            <a href="{{ route('tickets.assigned', ['overdue_filter' => 'overdue']) }}"
                                                class="underline font-semibold hover:text-red-900">View Assigned →</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Stats Grid (compact cards like localhost) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Overdue Tickets -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 mr-4">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Overdue</p>
                                <p class="text-2xl font-semibold text-red-600">
                                    {{ $overdueTickets->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Open Tickets -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 mr-4">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Open Tickets</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ Auth::user()->requestedTickets()->where('status_id', 1)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 mr-4">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">In Progress</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ Auth::user()->requestedTickets()->where('status_id', 2)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Closed Tickets -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 mr-4">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600">Closed Tickets</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ Auth::user()->requestedTickets()->where('status_id', 3)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manager: Change Delivery PIC Section -->
            @if (Auth::user()->department_id && (Auth::user()->is_pic || in_array(Auth::user()->status, ['Approver', 'Both'])))
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-user-check text-blue-600 text-2xl mr-3"></i>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Manage Delivery PIC (Batch Update)
                        </h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Change the delivery PIC assignment for multiple tickets at once (e.g., if someone is sick and
                        another PIC needs to sign the receipts).
                    </p>
                    <form id="changePicForm">
                        @csrf

                        <!-- Select Tickets Section -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-box mr-1"></i>Select Delivery Tickets
                            </label>

                            <!-- Search and Sort Controls -->
                            <div class="mb-3 flex gap-3">
                                <div class="flex-1">
                                    <input type="text" id="ticketSearch"
                                        placeholder="Search by REQ number, title, or PIC name..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                </div>
                                <select id="ticketSort"
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="req-asc">REQ Number (A-Z)</option>
                                    <option value="req-desc">REQ Number (Z-A)</option>
                                </select>
                            </div>

                            <div class="border border-gray-300 rounded-lg p-4 bg-white"
                                style="max-height: 250px; overflow-y: auto;" id="ticketListContainer">
                                @php
                                    $deliveryTickets = App\Models\Ticket::where(
                                        'department_id',
                                        Auth::user()->department_id,
                                    )
                                        ->whereHas('requestType', function ($q) {
                                            $q->whereIn('name', ['Delivery', 'Courier']);
                                        })
                                        ->whereHas('status', function ($q) {
                                            $q->whereNotIn('name', ['Completed', 'Closed', 'Cancelled']);
                                        })
                                        ->with('requestType', 'assignedTo')
                                        ->orderBy('created_at', 'desc')
                                        ->get();
                                @endphp

                                @if ($deliveryTickets->count() > 0)
                                    @foreach ($deliveryTickets as $ticket)
                                        <div class="flex items-center mb-2 ticket-item"
                                            data-req="{{ $ticket->request_number }}"
                                            data-title="{{ strtolower($ticket->title) }}"
                                            data-pic="{{ strtolower($ticket->delivery_pic_override ?? ($ticket->assignedTo->name ?? 'Unassigned')) }}"
                                            data-created="{{ $ticket->created_at->timestamp }}">
                                            <input type="checkbox" name="ticket_ids[]" value="{{ $ticket->id }}"
                                                id="ticket_{{ $ticket->id }}"
                                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                            <label for="ticket_{{ $ticket->id }}"
                                                class="ml-3 text-sm font-medium text-gray-700 cursor-pointer flex-1">
                                                <span class="font-semibold">{{ $ticket->request_number }}</span> -
                                                {{ $ticket->title }}
                                                <br>
                                                <small class="text-gray-500">Current PIC:
                                                    <strong>{{ $ticket->delivery_pic_override ?? ($ticket->assignedTo->name ?? 'Unassigned') }}</strong></small>
                                            </label>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-500 text-center py-4" id="noTicketsMessage">No active delivery
                                        tickets found</p>
                                @endif
                            </div>
                        </div>

                        <!-- Select New PIC Section -->
                        <div class="mb-6">
                            <label for="pic_user_select" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-tie mr-1"></i>Select New PIC from Department
                            </label>
                            @php
                                $picUsers = App\Models\User::where('department_id', Auth::user()->department_id)
                                    ->where('is_pic', 1)
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            <select id="pic_user_select" name="new_pic_user_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                                <option value="">-- Choose a PIC --</option>
                                @foreach ($picUsers as $user)
                                    <option value="{{ $user->id }}" data-name="{{ $user->name }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($picUsers->count() === 0)
                                <p class="text-red-600 text-sm mt-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    No PIC users found in your department
                                </p>
                            @endif
                        </div>

                        <button type="button" onclick="submitPicChangeBatch()"
                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <i class="fas fa-save mr-2"></i>Update PIC for Selected Tickets
                        </button>
                    </form>
                </div>
            @endif

        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mt-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('tickets.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Ticket
                    </a>
                    <a href="{{ route('tickets.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        View All Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function submitPicChangeBatch() {
            const ticketCheckboxes = document.querySelectorAll('input[name="ticket_ids[]"]:checked');
            const picUserSelect = document.getElementById('pic_user_select');

            const ticketIds = Array.from(ticketCheckboxes).map(cb => cb.value);
            const picUserId = picUserSelect.value;
            const picUserName = picUserSelect.options[picUserSelect.selectedIndex].dataset.name;

            if (ticketIds.length === 0) {
                alert('Please select at least one ticket');
                return;
            }

            if (!picUserId) {
                alert('Please select a PIC user');
                return;
            }

            // Submit batch update via AJAX
            const csrfToken = document.querySelector('input[name="_token"]').value;
            const updatePromises = ticketIds.map(ticketId =>
                fetch(`/tickets/${ticketId}/update-delivery-pic`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        delivery_pic_override: picUserName
                    })
                })
            );

            Promise.all(updatePromises)
                .then(responses => {
                    const allSuccess = responses.every(r => r.ok);
                    if (allSuccess) {
                        alert(`Successfully updated PIC to "${picUserName}" for ${ticketIds.length} ticket(s)!`);
                        document.getElementById('changePicForm').reset();
                        location.reload();
                    } else {
                        alert('Error updating some tickets');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating PIC');
                });
        }

        // Search and Sort functionality for delivery tickets
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('ticketSearch');
            const sortSelect = document.getElementById('ticketSort');
            const ticketContainer = document.getElementById('ticketListContainer');

            if (!searchInput || !sortSelect || !ticketContainer) return;

            function filterAndSortTickets() {
                const searchTerm = searchInput.value.toLowerCase();
                const sortValue = sortSelect.value;

                const ticketItems = Array.from(ticketContainer.querySelectorAll('.ticket-item'));
                let visibleCount = 0;

                // Filter tickets
                ticketItems.forEach(item => {
                    const reqNumber = item.dataset.req.toLowerCase();
                    const title = item.dataset.title;
                    const pic = item.dataset.pic;

                    const matches = reqNumber.includes(searchTerm) ||
                        title.includes(searchTerm) ||
                        pic.includes(searchTerm);

                    item.style.display = matches ? 'flex' : 'none';
                    if (matches) visibleCount++;
                });

                // Sort tickets
                ticketItems.sort((a, b) => {
                    switch (sortValue) {
                        case 'newest':
                            return parseInt(b.dataset.created) - parseInt(a.dataset.created);
                        case 'oldest':
                            return parseInt(a.dataset.created) - parseInt(b.dataset.created);
                        case 'req-asc':
                            return a.dataset.req.localeCompare(b.dataset.req);
                        case 'req-desc':
                            return b.dataset.req.localeCompare(a.dataset.req);
                        default:
                            return 0;
                    }
                });

                // Re-append sorted items
                ticketItems.forEach(item => ticketContainer.appendChild(item));

                // Show/hide no results message
                const noTicketsMessage = document.getElementById('noTicketsMessage');
                if (visibleCount === 0 && searchTerm) {
                    if (!document.getElementById('noResultsMessage')) {
                        const noResults = document.createElement('p');
                        noResults.id = 'noResultsMessage';
                        noResults.className = 'text-gray-500 text-center py-4';
                        noResults.textContent = 'No tickets match your search';
                        ticketContainer.appendChild(noResults);
                    }
                } else {
                    const noResultsMsg = document.getElementById('noResultsMessage');
                    if (noResultsMsg) noResultsMsg.remove();
                }
            }

            searchInput.addEventListener('input', filterAndSortTickets);
            sortSelect.addEventListener('change', filterAndSortTickets);
        });
    </script>
</x-app-layout>
