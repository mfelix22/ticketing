@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="text-3xl font-bold text-center text-green-700 mb-2">
                LAPORAN TICKETING
            </h1>
            @if ($startDate && $endDate)
                <h2 class="text-xl text-center text-gray-600">
                    PERIODE: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </h2>
            @else
                <h2 class="text-xl text-center text-gray-600">
                    PERIODE: Semua Data
                </h2>
            @endif
        </div>

        <!-- Summary Stats (Top) -->
        @if ($tickets->count() > 0)
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="text-muted mb-2">Total Tickets</h5>
                            <h2 class="text-primary mb-0">{{ $tickets->total() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="text-muted mb-2">Biaya Parkir</h5>
                            <h2 class="text-success mb-0">Rp {{ number_format($totalParkir, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="text-muted mb-2">Biaya Luar Kota</h5>
                            <h2 class="text-info mb-0">Rp {{ number_format($totalLuarKota, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="text-muted mb-2">Total Biaya</h5>
                            <h2 class="text-danger mb-0">Rp {{ number_format($totalParkir + $totalLuarKota, 0, ',', '.') }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="{{ $endDate }}">
                    </div>
                    <div class="col-md-2">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">-- Semua Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status_id" class="form-label">Status</label>
                        <select class="form-select" id="status_id" name="status_id">
                            <option value="">-- Semua Status --</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ $statusId == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary me-2 w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-12">
                        <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm me-2">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                        @if ($tickets->count() > 0)
                            <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'department_id' => $departmentId, 'status_id' => $statusId]) }}"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">CREATE
                                    DATE</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">NO REQUEST
                                </th>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">TITLE</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">REQUESTER
                                </th>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">CUSTOMER /
                                    RECIPIENT</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">ADDRESS
                                </th>
                                <th class="text-center align-middle"
                                    style="background-color: #e8f5e9; color: #2e7d32; border: 1px solid #81c784;">DESKRIPSI
                                </th>
                                <th class="text-center align-middle"
                                    style="background-color: #f8bbd0; color: #880e4f; border: 1px solid #f48fb1;">COURIER/
                                    EXPEDITION</th>
                                <th class="text-center align-middle"
                                    style="background-color: #f8bbd0; color: #880e4f; border: 1px solid #f48fb1;">HANDLING
                                    USER</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e1bee7; color: #4a148c; border: 1px solid #ce93d8;">SCHEDULE
                                    DATE</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e1bee7; color: #4a148c; border: 1px solid #ce93d8;">DUE
                                    DATE</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e1bee7; color: #4a148c; border: 1px solid #ce93d8;">FINISHED
                                    DATE</th>
                                <th colspan="2" class="text-center align-middle"
                                    style="background-color: #fff9c4; color: #f57f17; border: 1px solid #fff59d;">TOTAL
                                    BIAYA</th>
                                <th class="text-center align-middle"
                                    style="background-color: #b2ebf2; color: #006064; border: 1px solid #80deea;">TICKET
                                    STATUS</th>
                                <th class="text-center align-middle"
                                    style="background-color: #e0f2f1; color: #00695c; border: 1px solid #80cbc4;">LAMPIRAN
                                </th>
                                <th class="text-center align-middle"
                                    style="background-color: #e0f2f1; color: #00695c; border: 1px solid #80cbc4;">
                                    ADDITIONAL
                                    NOTES</th>
                            </tr>
                            <tr>
                                <th colspan="12" style="background-color: #f5f5f5; border: 1px solid #dee2e6;"></th>
                                <th class="text-center"
                                    style="background-color: #fff9c4; color: #f57f17; border: 1px solid #fff59d;">PARKIR
                                </th>
                                <th class="text-center"
                                    style="background-color: #fff9c4; color: #f57f17; border: 1px solid #fff59d;">L.KOTA
                                </th>
                                <th colspan="3" style="background-color: #f5f5f5; border: 1px solid #dee2e6;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $ticket)
                                @php
                                    $detailRequest = is_array($ticket->detail_request) ? $ticket->detail_request : [];

                                    // Get costs from database fields - handle string values properly
                                    $parkir = !empty($ticket->expenses_value) ? (float) $ticket->expenses_value : 0;
                                    $luarKota = !empty($ticket->other_costs) ? (float) $ticket->other_costs : 0;
                                    $totalBiaya = $parkir + $luarKota;

                                    // Get courier/expedition info
                                    $courierInfo = isset($detailRequest['delivery_type'])
                                        ? $detailRequest['delivery_type']
                                        : '';
                                    if (isset($detailRequest['courier_name'])) {
                                        $courierInfo .= ' - ' . $detailRequest['courier_name'];
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        {{ $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="text-center">{{ $ticket->request_number ?? '-' }}</td>
                                    <td>{{ $ticket->title ?? '-' }}</td>
                                    <td>{{ $ticket->requester ? $ticket->requester->name : '-' }}</td>
                                    <td>
                                        {{ isset($detailRequest['recipient_name'])
                                            ? $detailRequest['recipient_name']
                                            : (isset($detailRequest['customer_name'])
                                                ? $detailRequest['customer_name']
                                                : '-') }}
                                    </td>
                                    <td>
                                        {{ isset($detailRequest['delivery_address'])
                                            ? $detailRequest['delivery_address']
                                            : (isset($detailRequest['address'])
                                                ? $detailRequest['address']
                                                : '-') }}
                                    </td>
                                    <td
                                        style="max-width: 200px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.85rem;">
                                        {{ isset($detailRequest['package_description'])
                                            ? (is_array($detailRequest['package_description'])
                                                ? implode(', ', $detailRequest['package_description'])
                                                : $detailRequest['package_description'])
                                            : '-' }}
                                    </td>
                                    <td class="text-center" style="background-color: #fce4ec;">{{ $courierInfo ?: '-' }}
                                    </td>
                                    <td class="text-center">{{ $ticket->handling_user ?? '-' }}</td>
                                    <td class="text-center">
                                        {{ $ticket->schedule_date ? $ticket->schedule_date->format('d/m/Y') : '-' }}
                                        {{-- {{ $ticket->schedule_date ? $ticket->schedule_date->format('d/m/Y H:i') : '-' }} --}}
                                    </td>
                                    <td class="text-center">
                                        {{ $ticket->due_date ? $ticket->due_date->format('d/m/Y') : '-' }}
                                        {{-- {{ $ticket->due_date ? $ticket->due_date->format('d/m/Y H:i') : '-' }} --}}
                                    </td>
                                    <td class="text-center">
                                        {{ $ticket->finished_date ? $ticket->finished_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-end">{{ $parkir > 0 ? number_format($parkir, 0, ',', '.') : '0' }}
                                    </td>
                                    <td class="text-end">{{ $luarKota > 0 ? number_format($luarKota, 0, ',', '.') : '0' }}
                                    </td>
                                    <td class="text-center">
                                        @if ($ticket->status)
                                            <span
                                                class="badge 
                                                @if ($ticket->status->name === 'Completed') bg-success
                                                @elseif($ticket->status->name === 'In Progress') bg-primary
                                                @elseif($ticket->status->name === 'Pending') bg-warning
                                                @elseif($ticket->status->name === 'Cancelled') bg-danger
                                                @else bg-secondary @endif">
                                                {{ $ticket->status->name }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td
                                        style="max-width: 200px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.85rem;">
                                        {{ $ticket->receipt_comment ? substr($ticket->receipt_comment, 0, 100) . (strlen($ticket->receipt_comment) > 100 ? '...' : '') : '-' }}
                                    </td>
                                    <td
                                        style="max-width: 300px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.85rem;">
                                        {{ $ticket->additional_note ? substr($ticket->additional_note, 0, 100) . (strlen($ticket->additional_note) > 100 ? '...' : '') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">Tidak ada data untuk ditampilkan</p>
                                    </td>
                                </tr>
                            @endforelse

                            @if ($tickets->count() > 0)
                                <!-- Totals Row for Current Page -->
                                <tr style="background-color: #e3f2fd; font-weight: bold;">
                                    <td colspan="11"></td>
                                    <td class="text-end" style="background-color: #fff9c4;">TOTAL BIAYA</td>
                                    <td class="text-end" style="background-color: #fff9c4;">
                                        {{ number_format($pageParkir, 0, ',', '.') }}</td>
                                    <td class="text-end" style="background-color: #fff9c4;">
                                        {{ number_format($pageLuarKota, 0, ',', '.') }}</td>
                                    <td colspan="3"></td>
                                </tr>
                                <tr style="background-color: #e1f5fe; font-weight: bold; font-size: 1.1rem;">
                                    <td colspan="12" class="text-end" style="padding-right: 20px;"></td>
                                    <td colspan="2" class="text-center"
                                        style="background-color: #b3e5fc; color: #01579b;">
                                        {{ number_format($pageParkir + $pageLuarKota, 0, ',', '.') }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($tickets->count() > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan <strong>{{ $tickets->firstItem() }}</strong> hingga
                            <strong>{{ $tickets->lastItem() }}</strong> dari
                            <strong>{{ $tickets->total() }}</strong> data
                        </div>
                        <nav aria-label="Page navigation">
                            {{ $tickets->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <style>
        .table {
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            vertical-align: middle;
        }

        .table thead th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        @media print {
            .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }

            .btn,
            form {
                display: none !important;
            }
        }
    </style>
@endsection
