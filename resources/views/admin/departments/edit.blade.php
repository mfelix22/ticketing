@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
                    <li class="breadcrumb-item active">Edit Department</li>
                </ol>
            </nav>
            <h2 class="text-gray-800 font-semibold text-2xl mb-1">Edit Department: {{ $department->name }}</h2>
            <p class="text-gray-600 text-sm">Update department information</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gray-800 font-semibold">Department Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('departments.update', $department) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Department Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label text-gray-700 font-semibold">
                                    Department Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $department->name) }}" required
                                    placeholder="e.g., IT Support, Human Resources, Finance">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-gray-600">This will be visible to all users when creating tickets</small>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="form-label text-gray-700 font-semibold">
                                    Description
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="4" placeholder="Describe the department's role and responsibilities (optional)">{{ old('description', $department->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Department
                                </button>
                                <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="col-lg-4">
                <!-- Department Stats -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-info bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-info font-semibold">
                            <i class="fas fa-chart-bar me-2"></i>Department Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="text-sm text-gray-600">Total Tickets</div>
                            <div class="text-gray-800 font-semibold text-xl">
                                {{ $department->tickets()->count() }}
                            </div>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <div class="text-sm text-gray-600">Created</div>
                            <div class="text-gray-800 font-semibold">
                                {{ $department->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        <hr>
                        <div>
                            <div class="text-sm text-gray-600">Last Updated</div>
                            <div class="text-gray-800 font-semibold">
                                {{ $department->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning">
                        <h6 class="mb-0 text-warning font-semibold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Important
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-gray-600 mb-2">
                            Changing the department name will affect:
                        </p>
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>All tickets assigned to this department</li>
                            <li>Users assigned to this department</li>
                            <li>Department visibility in dropdowns</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
