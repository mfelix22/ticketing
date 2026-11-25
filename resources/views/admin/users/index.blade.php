@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="text-gray-800 font-semibold text-2xl mb-1">👥 User Management</h2>
                <p class="text-gray-600 text-sm">Manage system users, roles, and permissions</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create User
            </a>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Search -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search by name, username, email, or department...">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    @endif
                </form>
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
                                    <i class="fas fa-users text-primary fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Total Users</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $users->total() }}</div>
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
                                    <i class="fas fa-crown text-danger fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Super Admins</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $users->where('role', 'super_admin')->count() }}</div>
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
                                    <i class="fas fa-user-tie text-success fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">PICs</div>
                                <div class="text-2xl font-semibold text-gray-800">{{ $users->where('is_pic', 1)->count() }}
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
                                    <i class="fas fa-user text-info fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="text-gray-600 text-sm">Regular Users</div>
                                <div class="text-2xl font-semibold text-gray-800">
                                    {{ $users->where('role', 'user')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-gray-800 font-semibold">All Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">User</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Username</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Role</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Department</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">Status</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold">PIC</th>
                                <th class="px-4 py-3 text-gray-700 text-sm font-semibold text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <!-- User Info -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <span style="color: #ffffff; font-weight: 600;">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div style="color: #111827; font-weight: 600;">{{ $user->name }}</div>
                                                <div style="color: #6b7280; font-size: 0.875rem;">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Username -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-700">{{ $user->username }}</span>
                                    </td>

                                    <!-- Role Badge -->
                                    <td class="px-4 py-3">
                                        @if ($user->role === 'super_admin')
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                <i class="fas fa-crown me-1"></i>Super Admin
                                            </span>
                                        @elseif($user->role === 'manager')
                                            <span class="badge bg-purple bg-opacity-10 text-purple border border-purple">
                                                <i class="fas fa-chart-line me-1"></i>Manager
                                            </span>
                                        @elseif($user->role === 'operations_manager')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                                <i class="fas fa-cogs me-1"></i>Operations Manager
                                            </span>
                                        @elseif($user->role === 'general_manager')
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                                <i class="fas fa-user-graduate me-1"></i>General Manager
                                            </span>
                                        @elseif($user->role === 'admin')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">
                                                <i class="fas fa-user-shield me-1"></i>Admin
                                            </span>
                                        @elseif($user->role === 'pic')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <i class="fas fa-user-tie me-1"></i>PIC
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                                <i class="fas fa-user me-1"></i>User
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Department -->
                                    <td class="px-4 py-3">
                                        @if ($user->department)
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $user->department->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span class="text-gray-600 text-sm">{{ ucfirst($user->status) }}</span>
                                    </td>

                                    <!-- PIC Flag -->
                                    <td class="px-4 py-3">
                                        @if ($user->is_pic)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Yes
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-sm">No</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            @if ($user->id !== Auth::id())
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled
                                                    title="Cannot delete yourself">
                                                    <i class="fas fa-lock me-1"></i>Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-5 text-center text-gray-500">
                                        <i class="fas fa-users fa-3x mb-3 text-gray-300"></i>
                                        <p class="mb-0">No users found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
