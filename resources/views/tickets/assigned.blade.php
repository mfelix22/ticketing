@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-semibold text-2xl mb-1" style="color: #1f2937;">
                    @if (Auth::user()->is_pic)
                        🎯 Department Tickets
                    @else
                        📋 Assigned to Me
                    @endif
                </h2>
                <p class="text-sm" style="color: #6b7280;">
                    @if (Auth::user()->is_pic && Auth::user()->department)
                        Tickets assigned to {{ Auth::user()->department->name }} department
                    @else
                        Tickets you need to handle or approve
                    @endif
                </p>
            </div>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Ticket
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-fire text-danger fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Overdue</div>
                                <div class="text-2xl font-semibold" style="color: #dc3545;">
                                    {{ $tickets->filter(function ($t) {
                                            return $t->due_date &&
                                                $t->due_date->isPast() &&
                                                !in_array($t->status->name ?? '', ['Completed', 'Closed', 'Cancelled']);
                                        })->count() }}
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
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-tasks text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Total Assigned</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">{{ $tickets->total() }}</div>
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
                                    <i class="fas fa-clock text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Pending</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $tickets->filter(fn($t) => in_array($t->status->name ?? '', ['Open', 'In Progress']))->count() }}
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
                                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Urgent</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $tickets->filter(fn($t) => ($t->priority->name ?? '') === 'Urgent')->count() }}
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
                                    <i class="fas fa-check-circle text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Completed</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $tickets->filter(fn($t) => ($t->status->name ?? '') === 'Completed')->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('tickets.assigned') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="status" class="form-label text-gray-700 font-semibold">Filter by Status</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="" {{ request()->has('status') && request('status') == '' ? 'selected' : '' }}>All Statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->name }}"
                                    {{ (request('status') == $status->name || (!request()->has('status') && $status->name == 'Open')) ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="overdue_filter" class="form-label text-gray-700 font-semibold">Due Date</label>
                        <select name="overdue_filter" id="overdue_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">All Tickets</option>
                            <option value="overdue" {{ request('overdue_filter') == 'overdue' ? 'selected' : '' }}>
                                🔥 Overdue Only
                            </option>
                            <option value="due_soon" {{ request('overdue_filter') == 'due_soon' ? 'selected' : '' }}>
                                ⏰ Due Soon (24h)
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="handler_filter" class="form-label text-gray-700 font-semibold">Handler</label>
                        <select name="handler_filter" id="handler_filter" class="form-select"
                            onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="my_assignments"
                                {{ request('handler_filter') == 'my_assignments' ? 'selected' : '' }}>
                                👤 Mine Only
                            </option>
                            <option value="unassigned" {{ request('handler_filter') == 'unassigned' ? 'selected' : '' }}>
                                📥 Unassigned
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                        @if (request()->has('status') || request()->has('overdue_filter') || request()->has('handler_filter'))
                            <a href="{{ route('tickets.assigned') }}" class="btn btn-outline-danger ms-2">
                                <i class="fas fa-times me-2"></i>Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-gray-800 font-semibold">
                        @if (Auth::user()->is_pic)
                            Department Tickets
                        @else
                            My Assigned Tickets
                        @endif
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="listViewBtn"
                            onclick="switchView('list')">
                            <i class="fas fa-list me-1"></i>List View
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="groupViewBtn"
                            onclick="switchView('group')">
                            <i class="fas fa-layer-group me-1"></i>Group by Handler
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if ($tickets->count() > 0)
                    <!-- List View (Default) -->
                    <div id="listView">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827; width: 110px;">
                                            ID
                                        </th>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Title</th>
                                        <th class="px-4 py-3 text-sm font-semibold d-none d-lg-table-cell"
                                            style="color: #111827; width: 150px;">
                                            Requester</th>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827; width: 130px;">
                                            Handler</th>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827; width: 95px;">
                                            Priority</th>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827; width: 110px;">
                                            Status</th>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827; width: 130px;">
                                            Schedule Date</th>
                                        <th class="px-4 py-3 text-sm font-semibold" style="color: #111827; width: 130px;">
                                            Due Date</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-end"
                                            style="color: #111827; width: 90px;">Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tickets as $ticket)
                                        <tr>
                                            <!-- Ticket ID -->
                                            <td class="px-4 py-3">
                                                <span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary font-semibold">
                                                    {{ $ticket->request_number }}
                                                </span>
                                            </td>

                                            <!-- Title -->
                                            <td class="px-4 py-3">
                                                <a href="{{ route('tickets.show', $ticket) }}"
                                                    class="text-blue-600 hover:text-blue-800 font-medium text-decoration-none">
                                                    {{ Str::limit($ticket->title, 50) }}
                                                </a>
                                                @if ($ticket->attachments_count > 0)
                                                    <i class="fas fa-paperclip text-gray-400 ms-1"
                                                        title="Has attachments"></i>
                                                @endif
                                                @if (
                                                    $ticket->due_date &&
                                                        $ticket->schedule_date &&
                                                        $ticket->schedule_date > $ticket->due_date &&
                                                        !in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                                    <span class="badge bg-danger text-white ms-2 animate-pulse">
                                                        🔥 OVERDUE
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Requester -->
                                            <td class="px-4 py-3 d-none d-lg-table-cell">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                            <span class="text-xs font-semibold" style="color: #ffffff;">
                                                                {{ strtoupper(substr($ticket->requester->name ?? 'U', 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <div class="text-sm" style="color: #1f2937; font-weight: 500;">
                                                            {{ Str::limit($ticket->requester->name ?? 'Unknown', 20) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Handler -->
                                            <td class="px-4 py-3">
                                                @if ($ticket->handling_user_id)
                                                    {{-- Internal user handling --}}
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-cog text-primary me-1"
                                                            style="font-size: 0.85rem;"></i>
                                                        <span class="text-sm" style="color: #1f2937;">
                                                            {{ Str::limit($ticket->handlingUser?->name ?? $ticket->handling_user, 15) }}
                                                        </span>
                                                        @if ($ticket->handling_user_id === auth()->user()->id)
                                                            <span class="badge bg-success bg-opacity-10 text-success ms-1"
                                                                style="font-size: 0.6rem; padding: 2px 4px;">
                                                                YOU
                                                            </span>
                                                        @endif
                                                    </div>
                                                @elseif ($ticket->handling_user)
                                                    {{-- External handler (courier, etc.) --}}
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-shipping-fast text-warning me-1"
                                                            style="font-size: 0.85rem;"></i>
                                                        <span class="text-sm text-muted">
                                                            {{ Str::limit($ticket->handling_user, 15) }}
                                                        </span>
                                                    </div>
                                                @elseif ($ticket->receipt_user_id)
                                                    {{-- Approved, waiting assignment --}}
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-clock text-info me-1"
                                                            style="font-size: 0.85rem;"></i>
                                                        <span class="text-sm text-muted">
                                                            {{ Str::limit($ticket->receiptUser?->name ?? 'PIC', 15) }}
                                                        </span>
                                                    </div>
                                                @elseif ($ticket->assigned_to)
                                                    {{-- Assigned but not receipted yet --}}
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-tag text-secondary me-1"
                                                            style="font-size: 0.85rem;"></i>
                                                        <span class="text-sm text-muted">
                                                            {{ Str::limit($ticket->assignedTo?->name ?? 'Assigned', 15) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    {{-- Not assigned yet --}}
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary"
                                                        style="font-size: 0.75rem;">
                                                        <i class="fas fa-inbox me-1"></i>Unassigned
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Priority -->
                                            <td class="px-4 py-3">
                                                @php
                                                    $priorityName = $ticket->priority->name ?? 'Low';
                                                    $priorityClass = match ($priorityName) {
                                                        'Urgent' => 'bg-danger text-white',
                                                        'High' => 'bg-warning text-dark',
                                                        'Medium' => 'bg-info text-white',
                                                        default => 'bg-secondary text-white',
                                                    };
                                                @endphp
                                                <span class="badge {{ $priorityClass }}">
                                                    {{ $priorityName }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td class="px-4 py-3">
                                                @php
                                                    $statusName = $ticket->status->name ?? 'Open';
                                                    $statusClass = match ($statusName) {
                                                        'Open' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                        'In Progress'
                                                            => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                        'Completed' => 'bg-green-100 text-green-800 border-green-300',
                                                        'Closed' => 'bg-gray-100 text-gray-800 border-gray-300',
                                                        default => 'bg-gray-100 text-gray-800 border-gray-300',
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }} border">
                                                    {{ $statusName }}
                                                </span>
                                            </td>

                                            <!-- Schedule Date -->
                                            <td class="px-4 py-3">
                                                @if ($ticket->schedule_date)
                                                    <div class="text-sm" style="color: #1f2937;">
                                                        {{ $ticket->schedule_date->format('M d, Y H:i') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <!-- Due Date -->
                                            <td class="px-4 py-3">
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
                                                    <div>
                                                        <span
                                                            class="d-block text-sm {{ $isOverdue ? 'text-danger fw-bold' : ($isDueSoon ? 'text-warning fw-semibold' : 'text-gray-600') }}"
                                                            style="font-size: 0.875rem;">
                                                            {{ $ticket->due_date->format('M d') }}
                                                        </span>
                                                        <span
                                                            class="d-block text-xs {{ $isOverdue ? 'text-danger fw-bold' : ($isDueSoon ? 'text-warning' : 'text-gray-500') }}"
                                                            style="font-size: 0.7rem;">
                                                            @if ($isOverdue)
                                                                LATE - Beyond Deadline 🔥
                                                            @elseif ($isDueSoon)
                                                                @php
                                                                    $hours = floor($hoursUntilDue);
                                                                    $minutes = round(($hoursUntilDue - $hours) * 60);
                                                                @endphp
                                                                {{ $hours }}h {{ $minutes }}m ⏰
                                                            @else
                                                                {{ $ticket->due_date->diffForHumans() }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs">Not set</span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="px-4 py-3">
                                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                                    @if (
                                                        ($ticket->status->name ?? '') === 'Pending Cancellation' &&
                                                            (Auth::user()->is_pic || in_array(Auth::user()->status, ['Approver', 'Both', 'Super Admin'])))
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="showCancelReasonModal({{ $ticket->id }}, '{{ addslashes($ticket->request_number) }}', '{{ addslashes($ticket->title) }}')"
                                                            title="Review cancellation request">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Review Cancel
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('tickets.show', $ticket) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End List View -->

                    <!-- Group View (Hidden by default) -->
                    <div id="groupView" style="display: none;">
                        <div class="p-3">
                            @php
                                // Group tickets by handling user
                                $groupedTickets = $tickets->groupBy(function ($ticket) {
                                    if ($ticket->handling_user_id) {
                                        return 'internal_' . ($ticket->handlingUser->name ?? 'Unknown');
                                    } elseif ($ticket->handling_user) {
                                        return 'external_' . $ticket->handling_user;
                                    } elseif ($ticket->receipt_user_id) {
                                        return 'pic_' . ($ticket->receiptUser->name ?? 'Unknown');
                                    } elseif ($ticket->assigned_to) {
                                        return 'assigned_' . ($ticket->assignedTo->name ?? 'Unknown');
                                    } else {
                                        return 'unassigned';
                                    }
                                });
                            @endphp

                            <div>
                                @foreach ($groupedTickets as $handlerKey => $handlerTickets)
                                    @php
                                        $parts = explode('_', $handlerKey, 2);
                                        $type = $parts[0];
                                        $handlerName = $parts[1] ?? 'Unassigned';
                                        $count = $handlerTickets->count();

                                        // Determine icon and badge color based on type
                                        $icon = match ($type) {
                                            'internal' => 'fas fa-user-cog',
                                            'external' => 'fas fa-shipping-fast',
                                            'pic' => 'fas fa-user-clock',
                                            'assigned' => 'fas fa-user-tag',
                                            default => 'fas fa-inbox',
                                        };

                                        $badgeColor = match ($type) {
                                            'internal' => 'primary',
                                            'external' => 'warning',
                                            'pic' => 'info',
                                            'assigned' => 'secondary',
                                            default => 'secondary',
                                        };

                                        $accordionId = 'collapse' . $loop->index;
                                    @endphp

                                    <div class="card mb-2">
                                        <div class="card-header bg-light" id="heading{{ $loop->index }}"
                                            style="cursor: pointer;"
                                            onclick="(function(e) {
                                                 var target = document.getElementById('collapse{{ $loop->index }}');
                                                 var chevron = e.currentTarget.querySelector('.fa-chevron-down');
                                                 if (target.style.display === 'block') {
                                                     target.style.display = 'none';
                                                     if (chevron) chevron.style.transform = 'rotate(0deg)';
                                                 } else {
                                                     target.style.display = 'block';
                                                     if (chevron) chevron.style.transform = 'rotate(180deg)';
                                                 }
                                             })(event)">
                                            <h2 class="mb-0">
                                                <div
                                                    class="w-100 text-decoration-none d-flex justify-content-between align-items-center p-2">
                                                    <span>
                                                        <i class="{{ $icon }} me-2 text-{{ $badgeColor }}"></i>
                                                        <strong>{{ $handlerName }}</strong>
                                                        <span
                                                            class="badge bg-{{ $badgeColor }} ms-2">{{ $count }}
                                                            {{ Str::plural('ticket', $count) }}</span>
                                                    </span>
                                                    <i class="fas fa-chevron-down text-muted"
                                                        style="transition: transform 0.3s;"></i>
                                                </div>
                                            </h2>
                                        </div>
                                        <div id="collapse{{ $loop->index }}" style="display: none;">
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="px-3 py-2">ID</th>
                                                                <th class="px-3 py-2">Title</th>
                                                                <th class="px-3 py-2">Requester</th>
                                                                <th class="px-3 py-2">Priority</th>
                                                                <th class="px-3 py-2">Status</th>
                                                                <th class="px-3 py-2">Due Date</th>
                                                                <th class="px-3 py-2 text-end">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($handlerTickets as $ticket)
                                                                <tr>
                                                                    <td class="px-3 py-2">
                                                                        <span
                                                                            class="badge bg-secondary">{{ $ticket->request_number }}</span>
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        <a href="{{ route('tickets.show', $ticket) }}"
                                                                            class="text-decoration-none">
                                                                            {{ Str::limit($ticket->title, 40) }}
                                                                        </a>
                                                                        @if (
                                                                            $ticket->due_date &&
                                                                                $ticket->due_date->isPast() &&
                                                                                !in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                                                            <span
                                                                                class="badge bg-danger text-white ms-1">🔥</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2 text-sm">
                                                                        {{ $ticket->requester->name ?? 'Unknown' }}
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        @php
                                                                            $priorityName =
                                                                                $ticket->priority->name ?? 'Medium';
                                                                            $priorityClass = match ($priorityName) {
                                                                                'Urgent' => 'bg-danger text-white',
                                                                                'High' => 'bg-warning text-dark',
                                                                                'Medium' => 'bg-info text-white',
                                                                                'Low' => 'bg-secondary text-white',
                                                                                default => 'bg-secondary text-white',
                                                                            };
                                                                        @endphp
                                                                        <span
                                                                            class="badge {{ $priorityClass }}">{{ $priorityName }}</span>
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        @php
                                                                            $statusName =
                                                                                $ticket->status->name ?? 'Open';
                                                                            $statusClass = match ($statusName) {
                                                                                'Open' => 'bg-blue-100 text-blue-800',
                                                                                'In Progress'
                                                                                    => 'bg-yellow-100 text-yellow-800',
                                                                                'Completed'
                                                                                    => 'bg-green-100 text-green-800',
                                                                                'Closed' => 'bg-gray-100 text-gray-800',
                                                                                default => 'bg-gray-100 text-gray-800',
                                                                            };
                                                                        @endphp
                                                                        <span
                                                                            class="badge {{ $statusClass }}">{{ $statusName }}</span>
                                                                    </td>
                                                                    <td class="px-3 py-2">
                                                                        @if ($ticket->due_date)
                                                                            @php
                                                                                $isOverdue =
                                                                                    $ticket->due_date->isPast() &&
                                                                                    !in_array($ticket->status->name, [
                                                                                        'Completed',
                                                                                        'Closed',
                                                                                        'Cancelled',
                                                                                    ]);
                                                                            @endphp
                                                                            <span
                                                                                class="text-sm {{ $isOverdue ? 'text-danger fw-bold' : '' }}">{{ $ticket->due_date->format('M d') }}</span>
                                                                        @else
                                                                            <span class="text-muted text-xs">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2 text-end">
                                                                        <div
                                                                            class="d-flex gap-1 justify-content-end flex-wrap">
                                                                            @if (
                                                                                ($ticket->status->name ?? '') === 'Pending Cancellation' &&
                                                                                    (Auth::user()->is_pic || in_array(Auth::user()->status, ['Approver', 'Both', 'Super Admin'])))
                                                                                <button type="button"
                                                                                    class="btn btn-xs btn-warning"
                                                                                    onclick="showCancelReasonModal({{ $ticket->id }}, '{{ addslashes($ticket->request_number) }}', '{{ addslashes($ticket->title) }}')"
                                                                                    title="Review cancellation request">
                                                                                    <i
                                                                                        class="fas fa-exclamation-triangle"></i>
                                                                                </button>
                                                                            @endif
                                                                            <a href="{{ route('tickets.show', $ticket) }}"
                                                                                class="btn btn-xs btn-primary">
                                                                                <i class="fas fa-eye"></i>
                                                                            </a>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- End Group View -->

                    <!-- Pagination -->
                    @if ($tickets->hasPages())
                        <div class="card-footer bg-white border-top py-3">
                            {{ $tickets->withQueryString()->links() }}
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="px-6 py-12 text-center">
                        <div class="mb-4">
                            <i class="fas fa-inbox fa-4x text-gray-300"></i>
                        </div>
                        <h3 class="text-gray-800 font-semibold text-xl mb-2">No Tickets Assigned</h3>
                        <p class="text-gray-600 mb-4">
                            @if (Auth::user()->is_pic)
                                There are no tickets in your department yet.
                            @else
                                You don't have any assigned tickets at the moment.
                            @endif
                        </p>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>View All Tickets
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Help Card for PICs -->
        @if (Auth::user()->is_pic && $tickets->count() > 0)
            <div class="card border-0 shadow-sm mt-4 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-primary fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-gray-800 font-semibold mb-2">
                                <i class="fas fa-user-tie me-2"></i>You're a Department PIC
                            </h6>
                            <p class="text-gray-600 text-sm mb-2">
                                As a Person in Charge (PIC) for <strong>{{ Auth::user()->department->name }}</strong>,
                                you can view and manage all tickets submitted to your department.
                            </p>
                            <ul class="text-sm text-gray-600 mb-0 ps-3">
                                <li>View ticket details and comments</li>
                                <li>Update ticket status</li>
                                <li>Add comments and notes</li>
                                <li>Assign tickets to team members</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Cancellation Reason Review Modal -->
    <div id="cancelReasonModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Review Cancellation Request
                    </h5>
                    <button type="button" class="btn-close" onclick="closeCancelReasonModal()"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Ticket Information:</h6>
                        <div class="ps-3">
                            <p class="mb-1"><strong>Request #:</strong> <span id="modalRequestNumber"
                                    class="badge bg-primary"></span></p>
                            <p class="mb-0"><strong>Title:</strong> <span id="modalTitle"></span></p>
                        </div>
                    </div>
                    <div id="reasonLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading cancellation reason...</p>
                    </div>
                    <div id="reasonContentDiv" style="display: none;">
                        <div class="alert alert-info">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-comment-dots me-2"></i>Requester's Cancellation Reason:
                            </h6>
                            <div class="bg-white p-3 rounded border"
                                style="white-space: pre-wrap; word-wrap: break-word;">
                                <p id="cancelReasonContent" class="mb-0"></p>
                            </div>
                        </div>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Action:</strong> If you approve this cancellation, the ticket status will change to
                            "Cancelled by User".
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelReasonModal()">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                    <form id="approveCancelForm" method="POST" action="" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i>Approve Cancellation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function showCancelReasonModal(ticketId, requestNumber, title) {
            const modal = document.getElementById('cancelReasonModal');
            document.getElementById('modalRequestNumber').textContent = requestNumber;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('approveCancelForm').action = `{{ url('tickets') }}/${ticketId}/approve-cancel`;

            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');

            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'cancelReasonBackdrop';
            document.body.appendChild(backdrop);

            document.getElementById('reasonLoadingSpinner').style.display = 'block';
            document.getElementById('reasonContentDiv').style.display = 'none';

            try {
                const response = await fetch(`{{ url('tickets') }}/${ticketId}`);
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const comments = doc.querySelectorAll('.timeline-body');

                let cancelReason = 'No cancellation reason found.';
                comments.forEach(comment => {
                    const text = comment.textContent || '';
                    if (text.includes('CANCELLATION REQUESTED BY USER') || text.includes(
                            'Reason for Cancellation')) {
                        const lines = text.split('\n');
                        let foundLabel = false;
                        let reasonLines = [];
                        lines.forEach(line => {
                            if (foundLabel && line.trim()) reasonLines.push(line.trim());
                            if (line.includes('Reason for Cancellation:')) foundLabel = true;
                        });
                        if (reasonLines.length > 0) {
                            cancelReason = reasonLines.join(' ');
                        } else {
                            const parts = text.split('Reason for Cancellation:');
                            if (parts.length > 1) cancelReason = parts[1].trim();
                        }
                    }
                });
                document.getElementById('cancelReasonContent').textContent = cancelReason;
            } catch (e) {
                document.getElementById('cancelReasonContent').textContent =
                    'Error loading reason. Please view the ticket directly.';
            } finally {
                document.getElementById('reasonLoadingSpinner').style.display = 'none';
                document.getElementById('reasonContentDiv').style.display = 'block';
            }
        }

        function closeCancelReasonModal() {
            const modal = document.getElementById('cancelReasonModal');
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.getElementById('cancelReasonBackdrop');
            if (backdrop) backdrop.remove();
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeCancelReasonModal();
        });

        function switchView(viewType) {
            const listView = document.getElementById('listView');
            const groupView = document.getElementById('groupView');
            const listBtn = document.getElementById('listViewBtn');
            const groupBtn = document.getElementById('groupViewBtn');

            // Elements only exist when there are tickets; bail out if missing
            if (!listView || !groupView) return;

            if (viewType === 'list') {
                listView.style.display = 'block';
                groupView.style.display = 'none';
                if (listBtn) listBtn.classList.add('active');
                if (groupBtn) groupBtn.classList.remove('active');
                localStorage.setItem('assignedViewType', 'list');
            } else {
                listView.style.display = 'none';
                groupView.style.display = 'block';
                if (listBtn) listBtn.classList.remove('active');
                if (groupBtn) groupBtn.classList.add('active');
                localStorage.setItem('assignedViewType', 'group');
            }
        }

        // Restore saved view preference on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('assignedViewType') || 'list';
            switchView(savedView);
        });
    </script>
@endsection
