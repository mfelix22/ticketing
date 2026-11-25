@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-semibold text-2xl mb-1" style="color: #1f2937;">
                    <i class="fas fa-shipping-fast me-2"></i>Department Delivery Packages
                </h2>
                <p class="text-sm" style="color: #6b7280;">
                    All courier requests within your department (excluding completed)
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

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-users text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Department Packages</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">{{ $myTickets->total() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-box text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Total Packages</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">{{ $allTickets->total() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-shipping-fast text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Assigned</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $allTickets->where('handling_user', '!=', null)->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-question-circle text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Pending Assignment</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $allTickets->where('handling_user', null)->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($myTickets->count() > 0)
            <!-- Department Tickets Table -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white border-bottom py-3">
                    <h5 class="mb-0 font-semibold">
                        <i class="fas fa-users me-2"></i>Department Delivery Packages
                        <span class="badge bg-white text-primary ms-2">{{ $myTickets->total() }}</span>
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
                            <tbody>
                                @foreach ($myTickets as $ticket)
                                    @php
                                        $recipientName = $ticket->detail_request['recipient_name'] ?? '-';
                                        $phoneNumber = $ticket->detail_request['phone_number'] ?? '-';
                                        $address = $ticket->detail_request['delivery_address'] ?? '-';
                                    @endphp
                                    <tr class="table-primary">
                                        <td class="px-3 py-2"><span
                                                class="badge bg-primary">{{ $ticket->request_number }}</span></td>
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
                    <div class="px-3 py-2">{{ $myTickets->onEachSide(1)->links() }}</div>
                </div>
            </div>
        @endif

        <!-- All Tickets Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-gray-800 font-semibold">
                    <i class="fas fa-shipping-fast me-2 text-primary"></i>
                    All Delivery Requests
                    ({{ auth()->user()->role === 'super_admin' ? 'All Departments' : 'Today + Unassigned' }})
                    <span class="badge bg-secondary ms-2">{{ $allTickets->total() }}</span>
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
                            <tbody>
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
                    <div class="px-3 py-2">{{ $allTickets->onEachSide(1)->links() }}</div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No delivery requests</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');

            // Auto-submit on filter changes
            const filterElements = form.querySelectorAll('select, input[type="date"]');
            filterElements.forEach(element => {
                element.addEventListener('change', function() {
                    form.submit();
                });
            });

            // Debounced search
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    form.submit();
                }, 400);
            });

            // Handle Enter key for search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        });
    </script>
@endsection
