@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-gray-800 font-semibold text-2xl mb-1">🏢 Department Management</h2>
                <p class="text-gray-600 text-sm">Manage organizational departments</p>
            </div>
            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create Department
            </a>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Card -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-building text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Total Departments</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $departments->total() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-ticket-alt text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Total Tickets</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $departments->sum('tickets_count') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-chart-line text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Avg Tickets/Dept</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $departments->count() > 0 ? round($departments->sum('tickets_count') / $departments->count(), 1) : 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-gray-800 font-semibold">All Departments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Department Name</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Description</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold text-center">Tickets</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Created</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $department)
                                <tr>
                                    <!-- Department Name -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <i class="fas fa-building" style="color: #ffffff;"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div style="color: #111827; font-weight: 600;">{{ $department->name }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-600 text-sm">
                                            {{ $department->description ? Str::limit($department->description, 50) : '—' }}
                                        </span>
                                    </td>

                                    <!-- Ticket Count -->
                                    <td class="px-4 py-3 text-center">
                                        @if ($department->tickets_count > 0)
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                {{ $department->tickets_count }} tickets
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">No tickets</span>
                                        @endif
                                    </td>

                                    <!-- Created Date -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-600 text-sm">
                                            {{ $department->created_at->format('M d, Y') }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('departments.edit', $department) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <form action="{{ route('departments.destroy', $department) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure? This will affect {{ $department->tickets_count }} ticket(s).');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-5 text-center text-gray-500">
                                        <i class="fas fa-building fa-3x mb-3 text-gray-300"></i>
                                        <p class="mb-0">No departments found</p>
                                        <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-plus me-1"></i>Create First Department
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($departments->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $departments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
