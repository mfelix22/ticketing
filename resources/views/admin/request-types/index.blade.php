@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-gray-800 font-semibold text-2xl mb-1">📋 Request Type Management</h2>
                <p class="text-gray-600 text-sm">Manage ticket categories and request types</p>
            </div>
            <a href="{{ route('request-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create Request Type
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
                                    <i class="fas fa-list text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Total Request Types</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $requestTypes->total() }}</div>
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
                                <div class="text-2xl font-semibold text-gray-800">{{ $requestTypes->sum('tickets_count') }}
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
                                <div class="text-gray-600 text-sm">Avg Tickets/Type</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $requestTypes->count() > 0 ? round($requestTypes->sum('tickets_count') / $requestTypes->count(), 1) : 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Types Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-gray-800 font-semibold">All Request Types</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Request Type</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Description</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold text-center">Tickets</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Created</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requestTypes as $requestType)
                                <tr>
                                    <!-- Request Type Name -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <i class="fas fa-tag" style="color: #ffffff;"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div style="color: #111827; font-weight: 600;">{{ $requestType->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Description -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-600 text-sm">
                                            {{ $requestType->description ? Str::limit($requestType->description, 50) : '—' }}
                                        </span>
                                    </td>

                                    <!-- Ticket Count -->
                                    <td class="px-4 py-3 text-center">
                                        @if ($requestType->tickets_count > 0)
                                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                                {{ $requestType->tickets_count }} tickets
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">No tickets</span>
                                        @endif
                                    </td>

                                    <!-- Created Date -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-600 text-sm">
                                            {{ $requestType->created_at->format('M d, Y') }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('request-types.edit', $requestType) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <form action="{{ route('request-types.destroy', $requestType) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure? This will affect {{ $requestType->tickets_count }} ticket(s).');">
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
                                        <i class="fas fa-tags fa-3x mb-3 text-gray-300"></i>
                                        <p class="mb-0">No request types found</p>
                                        <a href="{{ route('request-types.create') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-plus me-1"></i>Create First Request Type
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($requestTypes->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $requestTypes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
