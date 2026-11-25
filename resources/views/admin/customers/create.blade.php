@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customer Management</a></li>
                    <li class="breadcrumb-item active">Create Customer</li>
                </ol>
            </nav>
            <h2 class="text-gray-800 font-semibold text-2xl mb-1">Create New Customer</h2>
            <p class="text-gray-600 text-sm">Add a new customer for Billing Collection delivery requests</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gray-800 font-semibold">Customer Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.customers.store') }}" method="POST">
                            @csrf

                            <!-- Customer Code -->
                            <div class="mb-3">
                                <label for="code" class="form-label text-gray-700 font-semibold">
                                    Customer Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" value="{{ old('code') }}" required
                                    placeholder="e.g., CUST001">
                                <small class="text-gray-600">Unique identifier for the customer</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Customer Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label text-gray-700 font-semibold">
                                    Customer Name (Company) <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required
                                    placeholder="e.g., PT. Maju Jaya">
                                <small class="text-gray-600">Company or organization name</small>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- PIC Name -->
                            <div class="mb-3">
                                <label for="pic_name" class="form-label text-gray-700 font-semibold">
                                    PIC Name (Recipient)
                                </label>
                                <input type="text" class="form-control @error('pic_name') is-invalid @enderror"
                                    id="pic_name" name="pic_name" value="{{ old('pic_name') }}"
                                    placeholder="e.g., John Doe">
                                <small class="text-gray-600">Person in charge name for delivery recipient</small>
                                @error('pic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3">
                                <label for="phone" class="form-label text-gray-700 font-semibold">
                                    Phone Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone') }}" required
                                    placeholder="e.g., 021-12345678">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label for="address" class="form-label text-gray-700 font-semibold">
                                    Address <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                    required placeholder="Enter complete address">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-gray-700 font-semibold" for="is_active">
                                        Active Customer
                                    </label>
                                    <br>
                                    <small class="text-gray-600">
                                        Inactive customers will not appear in the customer selection dropdown
                                    </small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Customer
                                </button>
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
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
                            <i class="fas fa-info-circle me-2"></i>Customer Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-gray-600">
                            Customers are used for Billing Collection (BC) delivery requests. When creating a delivery
                            ticket with BC department, users can select from the customer list to auto-fill recipient
                            information.
                        </p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-warning bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-warning font-semibold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Important
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>Customer code must be unique</li>
                            <li>All fields are required</li>
                            <li>Only active customers appear in dropdown</li>
                            <li>Phone number should include area code</li>
                            <li>Provide complete address for delivery</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
