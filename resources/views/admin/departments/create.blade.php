@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
                    <li class="breadcrumb-item active">Create Department</li>
                </ol>
            </nav>
            <h2 class="text-gray-800 font-semibold text-2xl mb-1">Create New Department</h2>
            <p class="text-gray-600 text-sm">Add a new department to organize your teams</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gray-800 font-semibold">Department Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('departments.store') }}" method="POST">
                            @csrf

                            <!-- Department Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label text-gray-700 font-semibold">
                                    Department Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required
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
                                    rows="4" placeholder="Describe the department's role and responsibilities (optional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Department
                                </button>
                                <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-primary font-semibold">
                            <i class="fas fa-info-circle me-2"></i>About Departments
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-gray-600">
                            Departments help organize your ticketing system by grouping related tickets together.
                        </p>
                        <hr>
                        <h6 class="text-gray-700 font-semibold mb-2">Common Departments:</h6>
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>IT Support</li>
                            <li>Human Resources</li>
                            <li>Finance</li>
                            <li>Operations</li>
                            <li>Customer Service</li>
                            <li>Marketing</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-success font-semibold">
                            <i class="fas fa-lightbulb me-2"></i>Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>Choose clear, descriptive names</li>
                            <li>Assign PICs to departments for better management</li>
                            <li>Department names must be unique</li>
                            <li>Users can be assigned to departments</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
