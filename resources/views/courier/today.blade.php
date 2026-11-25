@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-semibold text-2xl mb-1" style="color: #1f2937;">
                    <i class="fas fa-shipping-fast me-2"></i>Department Delivery Packages
                </h2>
                <p class="text-sm" style="color: #6b7280;">
                    @if (in_array(Auth::user()->role, ['super_admin', 'operations_manager']))
                        {{ request('department_id') ? 'Courier requests for selected department' : 'All courier requests across all departments' }}
                    @else
                        All courier requests within your department
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('courier.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>All Packages Report
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" id="filterForm" class="mb-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        @if (in_array(Auth::user()->role, ['super_admin', 'operations_manager']))
                            <div class="col-md-2">
                                <label class="form-label text-sm font-semibold mb-1">Department</label>
                                <select name="department_id" class="form-select form-select-sm">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <label class="form-label text-sm font-semibold mb-1">Time Period</label>
                            <select name="date_range" class="form-select form-select-sm" id="dateRange">
                                <option value="">Today + Unassigned</option>
                                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today Only
                                </option>
                                <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This
                                    Week</option>
                                <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>
                                    This Month</option>
                                <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>
                                    Last Month</option>
                                <option value="all_time" {{ request('date_range') == 'all_time' ? 'selected' : '' }}>All
                                    Time</option>
                                <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom
                                    Range</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-sm font-semibold mb-1">From Date</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-sm font-semibold mb-1">To Date</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-sm font-semibold mb-1">Handler</label>
                            <select name="courier" class="form-select form-select-sm">
                                <option value="">All Handlers</option>
                                @foreach ($courierNames as $handler)
                                    <option value="{{ $handler }}"
                                        {{ request('courier') == $handler ? 'selected' : '' }}>{{ $handler }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-sm font-semibold mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->name }}"
                                        {{ request('status') == $st->name ? 'selected' : '' }}>{{ $st->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-sm font-semibold mb-1">Assignment</label>
                            <select name="assignment" class="form-select form-select-sm">
                                <option value="">All Packages</option>
                                <option value="unassigned" {{ request('assignment') == 'unassigned' ? 'selected' : '' }}>
                                    Unassigned Only</option>
                                <option value="assigned" {{ request('assignment') == 'assigned' ? 'selected' : '' }}>
                                    Assigned Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter me-1"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Search (above tables) -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Search tickets by request #, title, requester, recipient, phone, address, courier"
                        id="searchInput">
                </div>
            </div>
        </form>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="text-center my-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Updating...</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <!-- Today's Packages -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-calendar-day text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-xs text-uppercase fw-bold mb-1" style="color: #6b7280;">Today's Packages
                                </div>
                                <div class="h3 mb-0 fw-bold" style="color: #1f2937;" id="stat-todayTotal">
                                    {{ $todayPackagesCount }}</div>
                                <div class="text-xs mt-1" style="color: #9ca3af;">
                                    <i class="fas fa-info-circle me-1"></i>Scheduled for today
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Unassigned -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-clock text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-xs text-uppercase fw-bold mb-1" style="color: #6b7280;">Today's
                                    Unassigned</div>
                                <div class="h3 mb-0 fw-bold" style="color: #1f2937;" id="stat-todayUnassigned">
                                    {{ $todayUnassignedCount }}</div>
                                <div class="text-xs mt-1" style="color: #9ca3af;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Needs handler today
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Unassigned (Backlog) -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-boxes text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-xs text-uppercase fw-bold mb-1" style="color: #6b7280;">Total Unassigned
                                </div>
                                <div class="h3 mb-0 fw-bold" style="color: #1f2937;" id="stat-totalUnassigned">
                                    {{ $totalUnassignedCount }}</div>
                                <div class="text-xs mt-1" style="color: #9ca3af;">
                                    <i class="fas fa-layer-group me-1"></i>All pending (backlog)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Unassigned -->
            <div class="col-md-3">
                <div
                    class="card border-0 shadow-sm h-100 {{ $overdueUnassignedCount > 0 ? 'border-danger border-2' : '' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-xs text-uppercase fw-bold mb-1" style="color: #6b7280;">Overdue
                                </div>
                                <div class="h3 mb-0 fw-bold text-danger" id="stat-overdueUnassigned">
                                    {{ $overdueUnassignedCount }}</div>
                                <div class="text-xs mt-1" style="color: #9ca3af;">
                                    <i class="fas fa-fire me-1"></i>Past due date
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($myTickets->count() > 0)
            <!-- Department Tickets Table -->
            <div class="card border-0 shadow-sm mb-4" id="myTicketsCard">
                <div class="card-header bg-primary text-white border-bottom py-3">
                    <h5 class="mb-0 font-semibold">
                        <i class="fas fa-users me-2"></i>
                        @if (in_array(Auth::user()->role, ['super_admin', 'operations_manager']))
                            {{ request('department_id') ? 'Selected Department' : 'All Departments' }} - Delivery Packages
                        @else
                            Department Delivery Packages
                        @endif
                        <span class="badge bg-white text-primary ms-2"
                            id="myTicketsTotal">{{ $myTickets->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 py-2">Request #</th>
                                    <th class="px-3 py-2">Title</th>
                                    <th class="px-3 py-2">Requester</th>
                                    <th class="px-3 py-2">Recipient</th>
                                    <th class="px-3 py-2">Phone</th>
                                    <th class="px-3 py-2">Address</th>
                                    <th class="px-3 py-2">Schedule Date</th>
                                    <th class="px-3 py-2">Courier</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="myTicketsBody">
                                @foreach ($myTickets as $ticket)
                                    @php
                                        $recipientName = $ticket->detail_request['recipient_name'] ?? '-';
                                        $phoneNumber = $ticket->detail_request['phone_number'] ?? '-';
                                        $address = $ticket->detail_request['delivery_address'] ?? '-';
                                        $finishedStatuses = ['Completed', 'Closed', 'Cancelled', 'Pending Approval'];
                                        $isOverdue =
                                            $ticket->due_date &&
                                            now()->greaterThan($ticket->due_date) &&
                                            !in_array($ticket->status->name ?? '', $finishedStatuses);
                                        $rowClass = $isOverdue ? 'table-danger' : 'table-primary';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="px-3 py-2">
                                            <span class="badge bg-primary">{{ $ticket->request_number }}</span>
                                            @if ($isOverdue)
                                                <span class="badge bg-danger ms-1"><i
                                                        class="fas fa-fire me-1"></i>Overdue</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">{{ Str::limit($ticket->title, 30) }}</td>
                                        <td class="px-3 py-2 text-sm">{{ $ticket->requester->name ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $recipientName }}</td>
                                        <td class="px-3 py-2">{{ $phoneNumber }}</td>
                                        <td class="px-3 py-2">{{ Str::limit($address, 40) }}</td>
                                        <td class="px-3 py-2 text-sm">
                                            @if ($ticket->schedule_date)
                                                <div>{{ $ticket->schedule_date->format('M d, H:i') }}</div>
                                            @else
                                                <div>-</div>
                                            @endif
                                            @if ($ticket->due_date)
                                                <div class="small text-muted">Due:
                                                    {{ $ticket->due_date->format('M d, H:i') }}</div>
                                            @endif
                                            @if ($isOverdue)
                                                <span class="badge bg-danger mt-1"><i
                                                        class="fas fa-fire me-1"></i>Overdue</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            @if ($ticket->handling_user)
                                                <span class="badge bg-info">{{ $ticket->handling_user }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Not assigned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            @php
                                                $statusClass = match ($ticket->status->name ?? 'Open') {
                                                    'Open' => 'bg-primary',
                                                    'In Progress' => 'bg-warning text-dark',
                                                    'Completed' => 'bg-success',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $statusClass }}">{{ $ticket->status->name ?? 'Open' }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-end">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-xs btn-primary"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('courier.label', $ticket) }}"
                                                class="btn btn-xs btn-success" target="_blank" title="Print Label">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            @php
                                                $canRequesterAct = Auth::user()->id === ($ticket->requester_id ?? null);
                                                $statusName = $ticket->status->name ?? 'Open';
                                                $canPicAct =
                                                    (Auth::user()->is_pic ?? false) ||
                                                    in_array(Auth::user()->status, ['Approver', 'Both', 'Super Admin']);
                                                $isPendingCancel = $statusName === 'Pending Cancellation';
                                                $isFinished = in_array($statusName, [
                                                    'Completed',
                                                    'Closed',
                                                    'Cancelled',
                                                    'Cancelled by PIC',
                                                    'Cancelled by User',
                                                ]);
                                            @endphp
                                            @if ($canPicAct && $isPendingCancel)
                                                <button type="button" class="btn btn-xs btn-danger"
                                                    onclick="showCancelReasonModal({{ $ticket->id }}, '{{ addslashes($ticket->request_number) }}', '{{ addslashes($ticket->title) }}')"
                                                    title="Review & Approve Cancellation">
                                                    <i class="fas fa-eye me-1"></i>Review Cancel
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-2 text-end" id="myTicketsPagination">{{ $myTickets->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- All Tickets Table -->
        {{-- <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-gray-800 font-semibold">
                    <i class="fas fa-shipping-fast me-2 text-primary"></i>
                    All Delivery Requests
                    ({{ auth()->user()->role === 'super_admin' ? 'All Departments' : 'Today + Unassigned' }})
                    <span class="badge bg-secondary ms-2" id="allTicketsTotal">{{ $allTickets->total() }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if ($allTickets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 py-2">Request #</th>
                                    <th class="px-3 py-2">Title</th>
                                    <th class="px-3 py-2">Requester</th>
                                    <th class="px-3 py-2">Recipient</th>
                                    <th class="px-3 py-2">Phone</th>
                                    <th class="px-3 py-2">Address</th>
                                    <th class="px-3 py-2">Schedule Date</th>
                                    <th class="px-3 py-2">Courier</th>
                                    <th class="px-3 py-2">Status</th>
                                    <th class="px-3 py-2 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="allTicketsBody">
                                @foreach ($allTickets as $ticket)
                                    @php
                                        $recipientName = $ticket->detail_request['recipient_name'] ?? '-';
                                        $phoneNumber = $ticket->detail_request['phone_number'] ?? '-';
                                        $address = $ticket->detail_request['delivery_address'] ?? '-';
                                        $isMyTicket =
                                            $ticket->assigned_to === auth()->id() ||
                                            $ticket->receipt_user_id === auth()->id() ||
                                            $ticket->handling_user_id === auth()->id();
                                    @endphp
                                    <tr
                                        class="{{ !$ticket->handling_user ? 'table-warning' : ($isMyTicket ? 'table-info' : '') }}">
                                        <td class="px-3 py-2"><span
                                                class="badge bg-secondary">{{ $ticket->request_number }}</span></td>
                                        <td class="px-3 py-2">{{ Str::limit($ticket->title, 30) }}</td>
                                        <td class="px-3 py-2 text-sm">{{ $ticket->requester->name ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $recipientName }}</td>
                                        <td class="px-3 py-2">{{ $phoneNumber }}</td>
                                        <td class="px-3 py-2">{{ Str::limit($address, 40) }}</td>
                                        <td class="px-3 py-2 text-sm">
                                            {{ $ticket->schedule_date ? $ticket->schedule_date->format('M d, H:i') : '-' }}
                                        </td>
                                        <td class="px-3 py-2">
                                            @if ($ticket->handling_user)
                                                <span class="badge bg-info">{{ $ticket->handling_user }}</span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Not assigned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">
                                            @php
                                                $statusClass = match ($ticket->status->name ?? 'Open') {
                                                    'Open' => 'bg-primary',
                                                    'In Progress' => 'bg-warning text-dark',
                                                    'Completed' => 'bg-success',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $statusClass }}">{{ $ticket->status->name ?? 'Open' }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-end">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-xs btn-primary"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('courier.label', $ticket) }}"
                                                class="btn btn-xs btn-success" target="_blank" title="Print Label">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-2 text-end" id="allTicketsPagination">{{ $allTickets->onEachSide(1)->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No delivery requests</p>
                    </div>
                @endif
            </div>
        </div> --}}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const loadingIndicator = document.getElementById('loadingIndicator');
            let currentMyPage = 1;
            let currentAllPage = 1;

            // Intercept initial server-rendered pagination links before first AJAX render
            function interceptInitialPagination() {
                ['myTicketsPagination', 'allTicketsPagination'].forEach(id => {
                    const container = document.getElementById(id);
                    if (!container) return;
                    container.querySelectorAll('a.page-link').forEach(a => {
                        a.addEventListener('click', (e) => {
                            e.preventDefault();
                            // Try to parse page from href query (?myPage= or ?allPage= or ?page=)
                            try {
                                const href = a.getAttribute('href') || '';
                                const url = new URL(href, window.location.origin);
                                const my = url.searchParams.get('myPage');
                                const all = url.searchParams.get('allPage');
                                const page = url.searchParams.get('page');
                                if (id === 'myTicketsPagination') {
                                    currentMyPage = parseInt(my || page || '1');
                                } else {
                                    currentAllPage = parseInt(all || page || '1');
                                }
                            } catch (err) {
                                // Fallback: if link has data attributes
                                const dataPage = a.dataset.page;
                                if (id === 'myTicketsPagination') {
                                    currentMyPage = parseInt(dataPage || '1');
                                } else {
                                    currentAllPage = parseInt(dataPage || '1');
                                }
                            }
                            fetchData();
                        });
                    });
                });
            }

            // Fetch and update data via AJAX - SEAMLESS like DataTables!
            async function fetchData() {
                loadingIndicator.style.display = 'block';

                // Build params manually to ensure all fields are included
                const params = new URLSearchParams();

                // Get all form fields
                const dateRange = form.querySelector('[name="date_range"]').value;
                const dateFrom = form.querySelector('[name="date_from"]').value;
                const dateTo = form.querySelector('[name="date_to"]').value;
                const courier = form.querySelector('[name="courier"]').value;
                const status = form.querySelector('[name="status"]').value;
                const assignment = form.querySelector('[name="assignment"]').value;
                const departmentId = form.querySelector('[name="department_id"]')?.value;
                const q = form.querySelector('[name="q"]').value;

                // Add all fields even if empty
                if (dateRange) params.append('date_range', dateRange);
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);
                if (courier) params.append('courier', courier);
                if (status) params.append('status', status);
                if (assignment) params.append('assignment', assignment);
                if (departmentId) params.append('department_id', departmentId);
                if (q) params.append('q', q);

                // Add pagination
                params.append('myPage', currentMyPage);
                params.append('allPage', currentAllPage);

                try {
                    const response = await fetch('{{ route('courier.today') }}?' + params, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();

                    // Debug: log first ticket to check data structure
                    if (data.allTickets.data.length > 0) {
                        console.log('Sample ticket data:', data.allTickets.data[0]);
                    }

                    // Update stats in real-time
                    document.getElementById('stat-todayTotal').textContent = data.stats.todayTotal;
                    document.getElementById('stat-todayUnassigned').textContent = data.stats.todayUnassigned;
                    document.getElementById('stat-totalUnassigned').textContent = data.stats.totalUnassigned;
                    document.getElementById('stat-overdueUnassigned').textContent = data.stats
                        .overdueUnassigned;

                    // Update overdue card styling
                    const overdueCard = document.getElementById('stat-overdueUnassigned').closest('.card');
                    if (data.stats.overdueUnassigned > 0) {
                        overdueCard.classList.add('border-danger', 'border-2');
                    } else {
                        overdueCard.classList.remove('border-danger', 'border-2');
                    }

                    // Update tables
                    updateMyTicketsTable(data.myTickets);
                    updateAllTicketsTable(data.allTickets);

                } catch (error) {
                    console.error('Error fetching data:', error);
                } finally {
                    loadingIndicator.style.display = 'none';
                }
            }

            function updateMyTicketsTable(tickets) {
                const tbody = document.getElementById('myTicketsBody');
                const card = document.getElementById('myTicketsCard');
                const total = document.getElementById('myTicketsTotal');

                if (total) total.textContent = tickets.total;

                if (tickets.data.length === 0) {
                    if (card) card.style.display = 'none';
                    return;
                }

                if (card) card.style.display = 'block';
                if (tbody) {
                    tbody.innerHTML = '';
                    tickets.data.forEach(ticket => {
                        tbody.appendChild(createTicketRow(ticket, true));
                    });
                }

                updatePagination('myTicketsPagination', tickets, 'myPage');
            }

            function updateAllTicketsTable(tickets) {
                const tbody = document.getElementById('allTicketsBody');
                const total = document.getElementById('allTicketsTotal');

                if (total) total.textContent = tickets.total;
                if (tbody) {
                    tbody.innerHTML = '';
                    tickets.data.forEach(ticket => {
                        tbody.appendChild(createTicketRow(ticket, false));
                    });
                }

                updatePagination('allTicketsPagination', tickets, 'allPage');
            }

            function createTicketRow(ticket, isDeptTicket) {
                const tr = document.createElement('tr');
                const finishedStatuses = ['Completed', 'Closed', 'Cancelled', 'Cancelled by PIC',
                    'Cancelled by User', 'Pending Approval'
                ];
                const nowTs = Date.now();
                const dueTs = ticket.due_date ? Date.parse(ticket.due_date) : null;
                const isOverdue = dueTs && nowTs > dueTs && !finishedStatuses.includes(ticket.status?.name || '');

                if (isDeptTicket) {
                    tr.className = isOverdue ? 'table-danger' : 'table-primary';
                } else if (!ticket.handling_user) {
                    tr.className = 'table-warning';
                }

                const recipientName = ticket.detail_request?.recipient_name || '-';
                const phoneNumber = ticket.detail_request?.phone_number || '-';
                const address = ticket.detail_request?.delivery_address || '-';
                const scheduleDate = ticket.schedule_date ? new Date(ticket.schedule_date).toLocaleDateString(
                    'en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '-';

                const statusClass = getStatusClass(ticket.status?.name);
                const courierBadge = ticket.handling_user ?
                    `<span class="badge bg-info">${ticket.handling_user}</span>` :
                    `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Not assigned</span>`;

                const dueDateHtml = ticket.due_date ?
                    `<div class="small text-muted">Due: ${new Date(ticket.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>` :
                    '';
                const overdueBadge = isOverdue ?
                    `<span class="badge bg-danger ms-1"><i class="fas fa-fire me-1"></i>Overdue</span>` :
                    '';

                tr.innerHTML = `
                    <td class="px-3 py-2">
                        <span class="badge ${isDeptTicket ? 'bg-primary' : 'bg-secondary'}">${ticket.request_number}</span>
                        ${overdueBadge}
                    </td>
                    <td class="px-3 py-2">${truncate(ticket.title, 30)}</td>
                    <td class="px-3 py-2 text-sm">${ticket.requester?.name || '-'}</td>
                    <td class="px-3 py-2">${recipientName}</td>
                    <td class="px-3 py-2">${phoneNumber}</td>
                    <td class="px-3 py-2">${truncate(address, 40)}</td>
                    <td class="px-3 py-2 text-sm">
                        <div>${scheduleDate}</div>
                        ${dueDateHtml}
                        ${isOverdue ? `<span class="badge bg-danger mt-1"><i class="fas fa-fire me-1"></i>Overdue</span>` : ''}
                    </td>
                    <td class="px-3 py-2">${courierBadge}</td>
                    <td class="px-3 py-2"><span class="badge ${statusClass}">${ticket.status?.name || 'Open'}</span></td>
                    <td class="px-3 py-2 text-end">
                        <a href="{{ url('tickets') }}/${ticket.id}" class="btn btn-xs btn-primary" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ url('courier/label') }}/${ticket.id}" class="btn btn-xs btn-success" target="_blank" title="Print Label">
                            <i class="fas fa-print"></i>
                        </a>
                        ${renderActionButtons(ticket)}
                    </td>
                `;

                return tr;
            }

            function updatePagination(containerId, tickets, pageName) {
                const container = document.getElementById(containerId);
                if (!container) return;

                if (tickets.last_page <= 1) {
                    container.innerHTML = '';
                    return;
                }

                let html = '<nav class="d-flex justify-content-end"><ul class="pagination mb-0">';
                html += `<li class="page-item ${tickets.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${tickets.current_page - 1}" data-type="${pageName}">Previous</a>
                </li>`;

                for (let i = 1; i <= tickets.last_page; i++) {
                    if (i === 1 || i === tickets.last_page || (i >= tickets.current_page - 1 && i <= tickets
                            .current_page + 1)) {
                        html += `<li class="page-item ${i === tickets.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}" data-type="${pageName}">${i}</a>
                        </li>`;
                    } else if (i === tickets.current_page - 2 || i === tickets.current_page + 2) {
                        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                html += `<li class="page-item ${tickets.current_page === tickets.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${tickets.current_page + 1}" data-type="${pageName}">Next</a>
                </li></ul></nav>`;

                container.innerHTML = html;

                container.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (this.parentElement.classList.contains('disabled')) return;

                        const page = parseInt(this.dataset.page);
                        const type = this.dataset.type;

                        if (type === 'myPage') currentMyPage = page;
                        else currentAllPage = page;

                        fetchData();
                    });
                });
            }

            function getStatusClass(status) {
                const map = {
                    'Open': 'bg-primary',
                    'In Progress': 'bg-warning text-dark',
                    'Completed': 'bg-success',
                    'Closed': 'bg-secondary',
                    'Cancelled': 'bg-danger',
                    'Pending Cancellation': 'bg-warning text-dark',
                    'Cancelled by PIC': 'bg-danger',
                    'Cancelled by User': 'bg-danger'
                };
                return map[status] || 'bg-secondary';
            }

            function truncate(str, length) {
                if (!str) return '-';
                return str.length > length ? str.substring(0, length) + '...' : str;
            }

            // Auto-filter on change - SEAMLESS!
            form.querySelectorAll('select, input[type="date"]').forEach(element => {
                console.log('Attaching event listener to:', element.name, element);
                element.addEventListener('change', function() {
                    console.log('Change detected on:', this.name);
                    currentMyPage = 1;
                    currentAllPage = 1;
                    fetchData();
                });
            });

            // Debounced search - 1 second delay like DataTables
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentMyPage = 1;
                    currentAllPage = 1;
                    fetchData();
                }, 1000);
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    currentMyPage = 1;
                    currentAllPage = 1;
                    fetchData();
                }
            });

            // Inject current user ID and role flags for frontend logic
            const CURRENT_USER_ID = {{ Auth::user()->id }};
            const CURRENT_USER_IS_PIC = {{ Auth::user()->is_pic ?? false ? 'true' : 'false' }};
            const CURRENT_USER_STATUS = '{{ addslashes(Auth::user()->status ?? '') }}';
            const CURRENT_USER_ROLE = '{{ addslashes(Auth::user()->role ?? '') }}';
            const CURRENT_USER_CAN_PIC_ACT = CURRENT_USER_IS_PIC || ['Approver', 'Both', 'Super Admin'].includes(
                CURRENT_USER_STATUS) || ['super_admin', 'operations_manager'].includes(CURRENT_USER_ROLE);
            const CURRENT_USER_CAN_EDIT_DUPLICATE = ['Requester', 'Both'].includes(CURRENT_USER_STATUS);

            function renderActionButtons(ticket) {
                const statusName = ticket.status?.name || 'Open';
                const isPendingCancel = statusName === 'Pending Cancellation';

                let html = '';
                if (CURRENT_USER_CAN_PIC_ACT && isPendingCancel) {
                    const requestNumber = ticket.request_number || 'N/A';
                    const title = ticket.title || 'Untitled';
                    html +=
                        `<button type="button" class="btn btn-xs btn-danger" onclick="showCancelReasonModal(${ticket.id}, '${requestNumber.replace(/'/g, "\\'")}', '${title.replace(/'/g, "\\'")}')"><i class="fas fa-eye me-1"></i>Review Cancel</button>`;
                }
                return html;
            }

            // Ensure first load uses AJAX and intercepts initial pagination
            interceptInitialPagination();
            fetchData();
        });

        // Cancellation Reason Modal Functions
        async function showCancelReasonModal(ticketId, requestNumber, title) {
            const modal = document.getElementById('cancelReasonModal');
            const modalRequestNumber = document.getElementById('modalRequestNumber');
            const modalTitle = document.getElementById('modalTitle');
            const cancelReasonContent = document.getElementById('cancelReasonContent');
            const approveForm = document.getElementById('approveCancelForm');
            const loadingSpinner = document.getElementById('reasonLoadingSpinner');
            const reasonContent = document.getElementById('reasonContentDiv');

            // Set ticket info
            modalRequestNumber.textContent = requestNumber;
            modalTitle.textContent = title;
            approveForm.action = `{{ url('tickets') }}/${ticketId}/approve-cancel`;

            // Show modal
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');

            // Create backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'cancelReasonBackdrop';
            document.body.appendChild(backdrop);

            // Show loading, hide content
            loadingSpinner.style.display = 'block';
            reasonContent.style.display = 'none';

            // Fetch cancellation reason from comments
            try {
                const response = await fetch(`{{ url('tickets') }}/${ticketId}`);
                const html = await response.text();

                // Parse HTML to extract cancellation reason comment
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const comments = doc.querySelectorAll('.timeline-body');

                let cancelReason = 'No cancellation reason found.';

                // Look for cancellation request comment
                comments.forEach(comment => {
                    const text = comment.textContent || '';
                    if (text.includes('CANCELLATION REQUESTED BY USER') || text.includes(
                            'Reason for Cancellation')) {
                        // Extract just the reason part
                        const lines = text.split('\n');
                        let foundReasonLabel = false;
                        let reasonLines = [];

                        lines.forEach(line => {
                            if (foundReasonLabel && line.trim()) {
                                reasonLines.push(line.trim());
                            }
                            if (line.includes('Reason for Cancellation:')) {
                                foundReasonLabel = true;
                            }
                        });

                        if (reasonLines.length > 0) {
                            cancelReason = reasonLines.join(' ');
                        } else if (text.includes('Reason for Cancellation:')) {
                            // Fallback: extract everything after "Reason for Cancellation:"
                            const parts = text.split('Reason for Cancellation:');
                            if (parts.length > 1) {
                                cancelReason = parts[1].trim();
                            }
                        }
                    }
                });

                cancelReasonContent.textContent = cancelReason;

            } catch (error) {
                console.error('Error fetching cancellation reason:', error);
                cancelReasonContent.textContent = 'Error loading cancellation reason. Please view the ticket details.';
            } finally {
                // Hide loading, show content
                loadingSpinner.style.display = 'none';
                reasonContent.style.display = 'block';
            }
        }

        function closeCancelReasonModal() {
            const modal = document.getElementById('cancelReasonModal');
            const backdrop = document.getElementById('cancelReasonBackdrop');

            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');

            if (backdrop) {
                backdrop.remove();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCancelReasonModal();
            }
        });
    </script>

    <!-- Cancellation Reason Review Modal -->
    <div id="cancelReasonModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Review Cancellation Request
                    </h5>
                    <button type="button" class="close" onclick="closeCancelReasonModal()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Ticket Information:</h6>
                        <div class="pl-3">
                            <p class="mb-1"><strong>Request #:</strong> <span id="modalRequestNumber"
                                    class="badge badge-primary"></span></p>
                            <p class="mb-0"><strong>Title:</strong> <span id="modalTitle"></span></p>
                        </div>
                    </div>

                    <div id="reasonLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading cancellation reason...</p>
                    </div>

                    <div id="reasonContentDiv" style="display: none;">
                        <div class="alert alert-info">
                            <h6 class="font-weight-bold mb-2">
                                <i class="fas fa-comment-dots mr-2"></i>Requester's Cancellation Reason:
                            </h6>
                            <div class="bg-white p-3 rounded border"
                                style="white-space: pre-wrap; word-wrap: break-word;">
                                <p id="cancelReasonContent" class="mb-0"></p>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Action:</strong> If you approve this cancellation, the ticket status will change to
                            "Cancelled by User".
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelReasonModal()">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                    <form id="approveCancelForm" method="POST" action="" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle mr-1"></i>Approve Cancellation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
