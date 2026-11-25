@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </nav>
            <h2 class="text-gray-800 font-semibold text-2xl mb-1">Edit User: {{ $user->name }}</h2>
            <p class="text-gray-600 text-sm">Update user information and permissions</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gray-800 font-semibold">User Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.users.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Personal Information Section -->
                            <div class="mb-4">
                                <h6 class="text-gray-700 font-semibold mb-3">Personal Information</h6>

                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label text-gray-700 font-semibold">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}" required
                                        placeholder="Enter full name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label text-gray-700 font-semibold">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $user->email) }}" required
                                        placeholder="user@example.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Company -->
                                <div class="mb-3">
                                    <label for="company" class="form-label text-gray-700 font-semibold">
                                        Company
                                    </label>
                                    <input type="text" class="form-control @error('company') is-invalid @enderror"
                                        id="company" name="company" value="{{ old('company', $user->company) }}"
                                        placeholder="Enter company name (optional)">
                                    @error('company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Login Credentials Section -->
                            <div class="mb-4">
                                <h6 class="text-gray-700 font-semibold mb-3">Login Credentials</h6>

                                <!-- Username -->
                                <div class="mb-3">
                                    <label for="username" class="form-label text-gray-700 font-semibold">
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                        id="username" name="username" value="{{ old('username', $user->username) }}"
                                        required placeholder="Enter username (lowercase, no spaces)">
                                    <small class="text-gray-600">This will be used for login</small>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label text-gray-700 font-semibold">
                                        New Password
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Leave blank to keep current password">
                                    <small class="text-gray-600">Only fill this if you want to change the password</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password Confirmation -->
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label text-gray-700 font-semibold">
                                        Confirm New Password
                                    </label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Re-enter new password">
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Role & Permissions Section -->
                            <div class="mb-4">
                                <h6 class="text-gray-700 font-semibold mb-3">Role & Permissions</h6>

                                <!-- Role -->
                                <div class="mb-3">
                                    <label for="role" class="form-label text-gray-700 font-semibold">
                                        User Role <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role"
                                        name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="super_admin"
                                            {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>
                                            🔴 Super Admin - Full system access
                                        </option>
                                        <option value="manager"
                                            {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>
                                            🟣 Manager - Can view and manage all Delivery requests within their assigned
                                            department
                                        </option>
                                        <option value="operations_manager"
                                            {{ old('role', $user->role) === 'operations_manager' ? 'selected' : '' }}>
                                            🟡 Operations Manager - Can view all Delivery requests across all departments
                                            and manage customers
                                        </option>
                                        <option value="general_manager"
                                            {{ old('role', $user->role) === 'general_manager' ? 'selected' : '' }}>
                                            🟤 General Manager - Can approve IT tickets as GM
                                        </option>
                                        <option value="admin"
                                            {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                                            🟠 Admin - System management
                                        </option>
                                        <option value="pic" {{ old('role', $user->role) === 'pic' ? 'selected' : '' }}>
                                            🟢 PIC - Department manager
                                        </option>
                                        <option value="user"
                                            {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                                            🔵 User - Regular user
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Department -->
                                <div class="mb-3">
                                    <label for="department_id" class="form-label text-gray-700 font-semibold">
                                        Department
                                    </label>
                                    <select class="form-select @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id">
                                        <option value="">No Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}"
                                                {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label text-gray-700 font-semibold">
                                        Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status"
                                        name="status" required>
                                        <option value="requester"
                                            {{ old('status', strtolower($user->status ?? '')) === 'requester' ? 'selected' : '' }}>
                                            Requester - Can create tickets
                                        </option>
                                        <option value="approver"
                                            {{ old('status', strtolower($user->status ?? '')) === 'approver' ? 'selected' : '' }}>
                                            Approver - Can approve tickets
                                        </option>
                                        <option value="both"
                                            {{ old('status', strtolower($user->status ?? '')) === 'both' ? 'selected' : '' }}>
                                            Both - Can create and approve
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Is PIC Checkbox (disabled if status is requester) -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_pic" name="is_pic"
                                            value="1" {{ old('is_pic', $user->is_pic) ? 'checked' : '' }}>
                                        <label class="form-check-label text-gray-700" for="is_pic">
                                            <strong>Person in Charge (PIC)</strong>
                                            <br>
                                            <small class="text-gray-600">
                                                This user can manage tickets in their assigned department
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var statusSelect = document.getElementById('status');
                                        var picCheckbox = document.getElementById('is_pic');

                                        function togglePicCheckbox() {
                                            if (statusSelect.value === 'requester') {
                                                picCheckbox.checked = false;
                                                picCheckbox.disabled = true;
                                            } else if (statusSelect.value === 'approver') {
                                                picCheckbox.checked = true;
                                                picCheckbox.disabled = false;
                                            } else {
                                                picCheckbox.disabled = false;
                                            }
                                        }
                                        statusSelect.addEventListener('change', togglePicCheckbox);
                                        togglePicCheckbox();
                                    });
                                </script>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update User
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="col-lg-4">
                <!-- User Stats -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-info bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-info font-semibold">
                            <i class="fas fa-chart-bar me-2"></i>User Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="text-sm text-gray-600">Member Since</div>
                            <div class="text-gray-800 font-semibold">
                                {{ $user->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <div class="text-sm text-gray-600">Last Updated</div>
                            <div class="text-gray-800 font-semibold">
                                {{ $user->updated_at->diffForHumans() }}
                            </div>
                        </div>
                        <hr>
                        <div>
                            <div class="text-sm text-gray-600">Current Role</div>
                            <div class="text-gray-800 font-semibold">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Guide -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-primary font-semibold">
                            <i class="fas fa-info-circle me-2"></i>Role Guide
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-danger font-semibold">🔴 Super Admin</h6>
                            <p class="text-sm text-gray-600 mb-0">
                                Full system access. Can manage users, departments, request types, and all tickets.
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="text-purple font-semibold">🟣 Manager</h6>
                            <p class="text-sm text-gray-600 mb-0">
                                Can view and manage all Delivery requests within their assigned department.
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="text-warning font-semibold">� Operations Manager</h6>
                            <p class="text-sm text-gray-600 mb-0">
                                Can view all Delivery requests across all departments and manage customers. Perfect for
                                operations coordination.
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="text-warning font-semibold">�🟠 Admin</h6>
                            <p class="text-sm text-gray-600 mb-0">
                                System management access. Reserved for future features.
                            </p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="text-success font-semibold">🟢 PIC</h6>
                            <p class="text-sm text-gray-600 mb-0">
                                Department manager. Can manage tickets in their assigned department.
                            </p>
                        </div>
                        <hr>
                        <div>
                            <h6 class="text-secondary font-semibold">🔵 User</h6>
                            <p class="text-sm text-gray-600 mb-0">
                                Regular user. Can create and view tickets within their assigned department.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
