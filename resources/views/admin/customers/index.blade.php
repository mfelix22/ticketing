@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-gray-800 font-semibold text-2xl mb-1">👥 Customer Management</h2>
                <p class="text-gray-600 text-sm">Manage customers for Billing Collection (BC) delivery requests</p>
            </div>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create Customer
            </a>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search Bar -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by customer name, code, PIC name, or phone..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
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
                                <div class="text-gray-600 text-sm">Total Customers</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $customers->total() }}</div>
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
                                    <i class="fas fa-check-circle text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Active</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $customers->where('is_active', 1)->count() }}
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
                                <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                                    <i class="fas fa-times-circle text-danger fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Inactive</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $customers->where('is_active', 0)->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-gray-800 font-semibold">All Customers</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Code</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Customer Name</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">PIC Name</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Phone</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Address</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Status</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <!-- Code -->
                                    <td class="px-4 py-3">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                            {{ $customer->code }}
                                        </span>
                                    </td>

                                    <!-- Customer Name -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <span style="color: #ffffff; font-weight: 600;">
                                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div style="color: #111827; font-weight: 600;">{{ $customer->name }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- PIC Name -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-700">
                                            @if ($customer->pic_name)
                                                <i class="fas fa-user me-1"></i>{{ $customer->pic_name }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Phone -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-700">
                                            <i class="fas fa-phone me-1"></i>{{ $customer->phone }}
                                        </span>
                                    </td>

                                    <!-- Address -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-600 text-sm">
                                            {{ Str::limit($customer->address, 50) }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        @if ($customer->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.customers.edit', $customer) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            {{-- <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            </form> --}}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-5 text-center text-gray-500">
                                        <i class="fas fa-building fa-3x mb-3 text-gray-300"></i>
                                        <p class="mb-0">No customers found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($customers->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
