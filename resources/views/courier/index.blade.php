@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-semibold text-2xl mb-1" style="color: #1f2937;">
                    <i class="fas fa-history me-2"></i>Delivery Package History
                </h2>
                <p class="text-sm" style="color: #6b7280;">
                    Complete history of all deliveries - track, analyze, and review past packages
                </p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-box text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Total in Period</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">{{ $tickets->count() }}</div>
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
                                    <i class="fas fa-truck text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Active Handlers</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $tickets->pluck('handling_user')->unique()->count() }}
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
                                <div class="text-sm" style="color: #6b7280;">Overdue</div>
                                <div class="text-2xl font-semibold" style="color: #dc3545;">
                                    {{ $tickets->filter(function ($t) {
                                            $isCompleted = in_array($t->status->name ?? '', ['Completed', 'Closed', 'Cancelled']);
                                            return !$isCompleted && $t->due_date && $t->schedule_date && $t->schedule_date > $t->due_date;
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
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-clock text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-sm" style="color: #6b7280;">Due Today</div>
                                <div class="text-2xl font-semibold" style="color: #1f2937;">
                                    {{ $tickets->filter(function ($t) {
                                            $isCompleted = in_array($t->status->name ?? '', ['Completed', 'Closed', 'Cancelled']);
                                            return !$isCompleted && $t->due_date && $t->due_date->isToday();
                                        })->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters (Sticky on scroll) -->
        <div class="card border-0 shadow-sm mb-4" style="position: sticky; top: 60px; z-index: 100; background: white;">
            <div class="card-body">
                <form method="GET" action="{{ route('courier.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label text-sm font-semibold mb-1">Time Period</label>
                        <select name="date_range" class="form-select form-select-sm">
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week
                            </option>
                            <option value="this_month"
                                {{ request('date_range', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month
                            </option>
                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last
                                Month</option>
                            <option value="all_time" {{ request('date_range') == 'all_time' ? 'selected' : '' }}>All Time
                            </option>
                            <option value="custom" {{ request('date_from') || request('date_to') ? 'selected' : '' }}>
                                Custom Range</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-sm font-semibold mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-sm font-semibold mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-sm font-semibold mb-1">Handler</label>
                        <select name="courier" class="form-select form-select-sm">
                            <option value="">All Handlers</option>
                            @foreach ($couriers as $courier)
                                <option value="{{ $courier }}"
                                    {{ request('courier') == $courier ? 'selected' : '' }}>
                                    {{ $courier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-sm font-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->name }}"
                                    {{ request('status') == $status->name ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            @if (request()->hasAny(['courier', 'status', 'date_from', 'date_to', 'date_range']))
                                <a href="{{ route('courier.index') }}" class="btn btn-outline-secondary btn-sm"
                                    title="Reset">
                                    <i class="fas fa-redo"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grouped by Courier -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-gray-800 font-semibold">
                    <i class="fas fa-layer-group me-2"></i>Packages by Delivery
                </h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="expandAllAccordions()">
                        <i class="fas fa-chevron-down me-1"></i>Expand All
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="collapseAllAccordions()">
                        <i class="fas fa-chevron-up me-1"></i>Collapse All
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                @if ($tickets->count() > 0)
                    <div id="courierGroups">
                        @foreach ($groups as $courierName => $courierTickets)
                            @php
                                $displayCourier = $courierName;
                                $hasNamedCourier = $displayCourier !== 'Unassigned';
                                $overdueCount = $courierTickets
                                    ->filter(function ($t) {
                                        $isCompleted = in_array($t->status->name ?? '', [
                                            'Completed',
                                            'Closed',
                                            'Cancelled',
                                        ]);
                                        return !$isCompleted && $t->due_date && $t->due_date->isPast();
                                    })
                                    ->count();
                                $todayCount = $courierTickets
                                    ->filter(function ($t) {
                                        $isCompleted = in_array($t->status->name ?? '', [
                                            'Completed',
                                            'Closed',
                                            'Cancelled',
                                        ]);
                                        return !$isCompleted && $t->due_date && $t->due_date->isToday();
                                    })
                                    ->count();
                                $openCount = $courierTickets->filter(fn($t) => $t->status->name === 'Open')->count();
                                $inProgressCount = $courierTickets
                                    ->filter(fn($t) => $t->status->name === 'In Progress')
                                    ->count();
                                $key = \Illuminate\Support\Str::slug($displayCourier);
                            @endphp

                            <div class="courier-group border rounded mb-2" data-key="{{ $key }}">
                                <div class="courier-toggle w-100 d-flex align-items-center p-3"
                                    style="background: {{ $hasNamedCourier ? '#f8f9fa' : '#fff3cd' }}; cursor: pointer; user-select: none;">
                                    <i
                                        class="fas fa-{{ $hasNamedCourier ? 'shipping-fast' : 'box-open' }} me-3 text-warning"></i>
                                    <div class="flex-grow-1 text-start">
                                        <strong class="fs-6">{{ $displayCourier }}</strong>
                                        <div class="small text-muted mt-1">
                                            <span class="me-3"><i
                                                    class="fas fa-box me-1"></i>{{ $courierTickets->count() }}
                                                total</span>
                                            <span class="me-3"><i class="fas fa-circle text-primary me-1"
                                                    style="font-size: 8px;"></i>{{ $openCount }} open</span>
                                            <span class="me-3"><i class="fas fa-circle text-warning me-1"
                                                    style="font-size: 8px;"></i>{{ $inProgressCount }} in progress</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 me-3" onclick="event.stopPropagation();">
                                        @if ($overdueCount > 0)
                                            <span class="badge bg-danger">{{ $overdueCount }} Overdue</span>
                                        @endif
                                        @if ($todayCount > 0)
                                            <span class="badge bg-info text-dark">{{ $todayCount }} Due Today</span>
                                        @endif
                                    </div>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>

                                <div class="courier-body" style="display: {{ $loop->first ? 'block' : 'none' }};">
                                    @php
                                        $activeManifestCount = $courierTickets
                                            ->filter(
                                                fn($t) => !in_array($t->status->name ?? '', [
                                                    'Completed',
                                                    'Closed',
                                                    'Cancelled',
                                                ]),
                                            )
                                            ->count();
                                    @endphp
                                    @if ($hasNamedCourier && $activeManifestCount > 0)
                                        <div class="d-flex justify-content-end mb-2 px-2 pt-2">
                                            <a href="{{ route('courier.manifest', $displayCourier) }}"
                                                class="btn btn-sm btn-outline-primary" target="_blank"
                                                onclick="event.stopPropagation();">
                                                <i class="fas fa-print me-1"></i>Print Manifest for {{ $displayCourier }}
                                            </a>
                                        </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="px-3 py-2">ID</th>
                                                    <th class="px-3 py-2">Title</th>
                                                    <th class="px-3 py-2">Requester</th>
                                                    <th class="px-3 py-2">Recipient</th>
                                                    <th class="px-3 py-2">Phone</th>
                                                    <th class="px-3 py-2">Address</th>
                                                    <th class="px-3 py-2">Schedule Date</th>
                                                    <th class="px-3 py-2">Courier</th>
                                                    <th class="px-3 py-2">Priority</th>
                                                    <th class="px-3 py-2">Status</th>
                                                    <th class="px-3 py-2">Due/Completed</th>
                                                    <th class="px-3 py-2 text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($courierTickets->sortBy('due_date') as $ticket)
                                                    @php
                                                        $isCompleted = in_array($ticket->status->name ?? '', [
                                                            'Completed',
                                                            'Closed',
                                                            'Cancelled',
                                                        ]);
                                                        $isOverdue =
                                                            !$isCompleted &&
                                                            $ticket->due_date &&
                                                            $ticket->schedule_date &&
                                                            $ticket->schedule_date > $ticket->due_date;
                                                        $recipientName =
                                                            $ticket->detail_request['recipient_name'] ?? '-';
                                                        $phoneNumber = $ticket->detail_request['phone_number'] ?? '-';
                                                        $address = $ticket->detail_request['delivery_address'] ?? '-';
                                                    @endphp
                                                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}"
                                                        style="cursor: pointer;"
                                                        onclick="if (event.target.tagName !== 'A' && event.target.tagName !== 'BUTTON' && event.target.tagName !== 'I') { window.location='{{ route('tickets.show', $ticket) }}'; }">
                                                        <td class="px-3 py-2"><span
                                                                class="badge bg-secondary">{{ $ticket->request_number }}</span>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            {{ Str::limit($ticket->title, 40) }}
                                                            @if ($isOverdue)
                                                                <span class="badge bg-danger ms-1">🔥 Overdue</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-sm">
                                                            {{ $ticket->requester->name ?? '-' }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ $recipientName }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ $phoneNumber }}</td>
                                                        <td class="px-3 py-2 text-sm">{{ Str::limit($address, 30) }}</td>
                                                        <td class="px-3 py-2 text-sm">
                                                            @if ($ticket->schedule_date)
                                                                {{ $ticket->schedule_date->format('M d, H:i') }}
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-sm">
                                                            @if ($ticket->handling_user)
                                                                {{ $ticket->handling_user }}
                                                            @else
                                                                <span class="text-warning">⚠️ Not assigned</span>
                                                            @endif
                                                        </td>

                                                        <td class="px-3 py-2">
                                                            @php
                                                                $priorityClass = match (
                                                                    $ticket->priority->name ?? 'Medium'
                                                                ) {
                                                                    'Urgent' => 'bg-danger',
                                                                    'High' => 'bg-warning',
                                                                    'Medium' => 'bg-info',
                                                                    'Low' => 'bg-secondary',
                                                                    default => 'bg-secondary',
                                                                };
                                                            @endphp
                                                            <span
                                                                class="badge {{ $priorityClass }}">{{ $ticket->priority->name ?? 'Medium' }}</span>
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
                                                        <td class="px-3 py-2">
                                                            @if (in_array($ticket->status->name ?? '', ['Completed', 'Closed']))
                                                                @if ($ticket->updated_at)
                                                                    <span class="text-success">
                                                                        <i
                                                                            class="fas fa-check-circle me-1"></i>{{ $ticket->updated_at->format('M d, H:i') }}
                                                                    </span>
                                                                    <br><small class="text-muted">Completed</small>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            @else
                                                                @if ($ticket->due_date)
                                                                    <span
                                                                        class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">{{ $ticket->due_date->format('M d, H:i') }}</span>
                                                                    @if ($ticket->due_date->isToday())
                                                                        <br><small class="text-warning">⏰ Today</small>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-end">
                                                            <a href="{{ route('tickets.show', $ticket) }}"
                                                                class="btn btn-xs btn-primary"
                                                                onclick="event.stopPropagation();">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x mb-3" style="color: #d1d5db;"></i>
                        <h5 class="text-muted">No delivery packages found</h5>
                        <p class="text-muted small">Try adjusting your filters or check back later.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Custom accordion logic without Bootstrap collapse (prevents double-toggle flicker)
        (function() {
            const storageKey = 'courierOpenKeys';
            const container = document.getElementById('courierGroups');
            if (!container) return;

            function getOpenKeys() {
                try {
                    return JSON.parse(localStorage.getItem(storageKey) || '[]');
                } catch {
                    return [];
                }
            }

            function setOpenKeys(keys) {
                localStorage.setItem(storageKey, JSON.stringify(keys));
            }

            function toggleRow(row) {
                const body = row.querySelector('.courier-body');
                const icon = row.querySelector('.toggle-icon');
                const key = row.getAttribute('data-key');
                const openKeys = new Set(getOpenKeys());
                const willOpen = body.style.display === 'none' || body.style.display === '';
                body.style.display = willOpen ? 'block' : 'none';
                if (icon) icon.className = 'fas ' + (willOpen ? 'fa-chevron-up' : 'fa-chevron-down') + ' toggle-icon';
                if (key) {
                    willOpen ? openKeys.add(key) : openKeys.delete(key);
                    setOpenKeys(Array.from(openKeys));
                }
            }

            // Initialize listeners - entire header is clickable
            container.querySelectorAll('.courier-group').forEach(group => {
                const toggle = group.querySelector('.courier-toggle');
                if (toggle) {
                    toggle.addEventListener('click', (e) => {
                        // Ignore clicks on links inside header (manifest button)
                        if (e.target.closest('a')) return;
                        toggleRow(group);
                    });
                }
            });

            // Restore state
            const openKeys = new Set(getOpenKeys());
            container.querySelectorAll('.courier-group').forEach(group => {
                const key = group.getAttribute('data-key');
                const body = group.querySelector('.courier-body');
                const icon = group.querySelector('.toggle-icon');
                const shouldOpen = openKeys.has(key);
                body.style.display = shouldOpen ? 'block' : body.style.display || 'none';
                if (icon) icon.className = 'fas ' + (shouldOpen ? 'fa-chevron-up' : 'fa-chevron-down') +
                    ' toggle-icon';
            });

            // Global controls
            window.expandAllAccordions = function() {
                const keys = [];
                container.querySelectorAll('.courier-group').forEach(group => {
                    const key = group.getAttribute('data-key');
                    const body = group.querySelector('.courier-body');
                    const icon = group.querySelector('.toggle-icon');
                    if (body) body.style.display = 'block';
                    if (icon) icon.className = 'fas fa-chevron-up toggle-icon';
                    if (key) keys.push(key);
                });
                setOpenKeys(keys);
            };
            window.collapseAllAccordions = function() {
                container.querySelectorAll('.courier-group').forEach(group => {
                    const body = group.querySelector('.courier-body');
                    const icon = group.querySelector('.toggle-icon');
                    if (body) body.style.display = 'none';
                    if (icon) icon.className = 'fas fa-chevron-down toggle-icon';
                });
                setOpenKeys([]);
            };
        })();
    </script>
@endsection
