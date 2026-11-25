@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="mb-4">
            <h1 class="text-3xl font-bold text-center text-blue-700 mb-2">
                IT Ticket Report
            </h1>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.it') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status_id" class="form-label">Status</label>
                        <select class="form-select" id="status_id" name="status_id">
                            <option value="">-- All Status --</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ $statusId == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="requester_id" class="form-label">Requester</label>
                        <select class="form-select" id="requester_id" name="requester_id">
                            <option value="">-- All Requesters --</option>
                            @foreach ($requesters as $requester)
                                <option value="{{ $requester->id }}" {{ $requesterId == $requester->id ? 'selected' : '' }}>
                                    {{ $requester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2 w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        @if ($tickets->count() > 0)
                            <a href="{{ route('reports.it.export', ['start_date' => $startDate, 'end_date' => $endDate, 'status_id' => $statusId, 'requester_id' => $requesterId]) }}"
                                class="btn btn-success w-100">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th>No Ticket (ID)</th>
                                <th>Title</th>
                                <th>Requester</th>
                                <th>Handler</th>
                                <th>Priority</th>
                                <th>Request Details</th>
                                <th>Completion Note</th>
                                <th>Status</th>
                                <th>Ticket Created</th>
                                <th>Ticket Approved</th>
                                <th>Ticket Finished</th>
                                <th>Duration (Hours)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->request_number }}</td>
                                    <td>{{ $ticket->title }}</td>
                                    <td>{{ $ticket->requester ? $ticket->requester->name : '-' }}</td>
                                    <td>{{ $ticket->handling_user ?? '-' }}</td>
                                    <td>{{ $ticket->priority ? $ticket->priority->name : '-' }}</td>
                                    <td>
                                        @if (is_array($ticket->detail_request) && count($ticket->detail_request) > 0)
                                            @foreach ($ticket->detail_request as $key => $value)
                                                <div><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                                            @endforeach
                                        @elseif (is_string($ticket->detail_request) && !empty($ticket->detail_request))
                                            {{ $ticket->detail_request }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $ticket->note ?? '-' }}</td>
                                    <td>{{ $ticket->status ? $ticket->status->name : '-' }}</td>
                                    <td>{{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $ticket->receipt_date ? $ticket->receipt_date->format('d/m/Y H:i') : ($ticket->acceptance_date ? $ticket->acceptance_date->format('d/m/Y H:i') : '-') }}</td>
                                    <td>{{ $ticket->finished_date ? $ticket->finished_date->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if (!is_null($ticket->duration_seconds) && $ticket->duration_seconds >= 0)
                                            @php
                                                $hours = floor($ticket->duration_seconds / 3600);
                                                $minutes = floor(($ticket->duration_seconds % 3600) / 60);
                                            @endphp
                                            @if ($hours > 0)
                                                {{ $hours }} hour{{ $hours > 1 ? 's' : '' }}
                                            @endif
                                            @if ($hours > 0 && $minutes > 0)
                                            @endif
                                            @if ($minutes > 0 || $hours == 0)
                                                {{ $minutes }} min{{ $minutes != 1 ? 's' : '' }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No IT tickets found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $tickets->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
