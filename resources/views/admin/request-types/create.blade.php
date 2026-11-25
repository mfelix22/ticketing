@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('request-types.index') }}">Request Types</a></li>
                    <li class="breadcrumb-item active">Create Request Type</li>
                </ol>
            </nav>
            <h2 class="text-gray-800 font-semibold text-2xl mb-1">Create New Request Type</h2>
            <p class="text-gray-600 text-sm">Add a new category for tickets</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gray-800 font-semibold">Request Type Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('request-types.store') }}" method="POST">
                            @csrf

                            <!-- Request Type Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label text-gray-700 font-semibold">
                                    Request Type Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required
                                    placeholder="e.g., Bug Report, Feature Request, Technical Issue">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-gray-600">This will appear in the ticket creation form</small>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="form-label text-gray-700 font-semibold">
                                    Description
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="4" placeholder="Describe when users should choose this request type (optional)">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Request Type
                                </button>
                                <a href="{{ route('request-types.index') }}" class="btn btn-outline-secondary">
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
                            <i class="fas fa-info-circle me-2"></i>About Request Types
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-gray-600">
                            Request types help categorize tickets by the nature of the request.
                        </p>
                        <hr>
                        <h6 class="text-gray-700 font-semibold mb-2">Common Request Types:</h6>
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>Bug Report</li>
                            <li>Feature Request</li>
                            <li>Technical Support</li>
                            <li>Access Request</li>
                            <li>Equipment Request</li>
                            <li>Software Installation</li>
                            <li>Password Reset</li>
                            <li>Network Issue</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-success font-semibold">
                            <i class="fas fa-lightbulb me-2"></i>Best Practices
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>Use clear, specific names</li>
                            <li>Keep the list focused (5-10 types)</li>
                            <li>Add descriptions for clarity</li>
                            <li>Request type names must be unique</li>
                            <li>Consider your team's workflow</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
