@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-2">
                <a href="{{ route('admin.templates.index') }}" class="btn btn-link text-decoration-none p-0 mr-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i>Create Template
                </h1>
            </div>
            <p class="text-muted mb-0">Create a reusable template for common ticket requests</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ route('admin.templates.store') }}" method="POST">
                            @csrf
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-triangle-exclamation mr-2"></i>There were some problems with your input.
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            @endif

                            <!-- Template Name -->
                            <div class="form-group mb-4">
                                <label for="name" class="font-weight-semibold">
                                    Template Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}"
                                    placeholder="e.g., Laptop Screen Repair Request" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="form-group mb-4">
                                <label for="description" class="font-weight-semibold">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="2" placeholder="Brief description of what this template is for">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Request Type -->
                            <div class="form-group mb-4">
                                <label for="request_type_id" class="font-weight-semibold">
                                    Request Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('request_type_id') is-invalid @enderror"
                                    id="request_type_id" name="request_type_id" required>
                                    <option value="">-- Select Request Type --</option>
                                    @foreach ($requestTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('request_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('request_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Department (Optional) -->
                            <div class="form-group mb-4">
                                <label for="department_id" class="font-weight-semibold">
                                    Department <small class="text-muted">(Optional - Leave blank for all
                                        departments)</small>
                                </label>
                                <select class="form-control @error('department_id') is-invalid @enderror" id="department_id"
                                    name="department_id">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Title Template -->
                            <div class="form-group mb-4">
                                <label for="title_template" class="font-weight-semibold">
                                    Default Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('title_template') is-invalid @enderror"
                                    id="title_template" name="title_template" value="{{ old('title_template') }}"
                                    placeholder="e.g., Laptop screen replacement needed" required>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>This will be the default title when users select
                                    this template
                                </small>
                                @error('title_template')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Default Priority -->
                            <div class="form-group mb-4">
                                <label for="default_priority_id" class="font-weight-semibold">
                                    Default Priority <small class="text-muted">(Optional)</small>
                                </label>
                                <select class="form-control @error('default_priority_id') is-invalid @enderror"
                                    id="default_priority_id" name="default_priority_id">
                                    <option value="">No default priority</option>
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->id }}"
                                            {{ old('default_priority_id') == $priority->id ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_priority_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Field Defaults -->
                            <div class="form-group mb-4">
                                <label class="font-weight-semibold">
                                    Default Field Values <small class="text-muted">(Optional)</small>
                                </label>
                                <div id="field-defaults-container" class="p-3 bg-light rounded">
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Select a Request Type above to configure default field values
                                    </p>
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="form-group mb-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        <strong>Active</strong>
                                        <small class="text-muted d-block">Enable this template for users to select</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i>Create Template
                                </button>
                                <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-question-circle mr-2"></i>Template Help
                    </div>
                    <div class="card-body">
                        <h6 class="font-weight-bold">What are templates?</h6>
                        <p class="text-sm text-muted">
                            Templates help users create tickets faster by pre-filling common information.
                        </p>

                        <h6 class="font-weight-bold mt-3">When to use templates?</h6>
                        <ul class="text-sm text-muted">
                            <li>Recurring requests (e.g., laptop repairs)</li>
                            <li>Standard procedures (e.g., office supplies)</li>
                            <li>Common issues with known solutions</li>
                        </ul>

                        <h6 class="font-weight-bold mt-3">Best practices:</h6>
                        <ul class="text-sm text-muted">
                            <li>Use clear, descriptive names</li>
                            <li>Set appropriate default priorities</li>
                            <li>Pre-fill common field values</li>
                            <li>Keep templates active and updated</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Base URL for subdirectory deployments (e.g., /ticketing)
        const APP_BASE_URL = "{{ rtrim(url('/'), '/') }}";

        // Load request type fields when type is selected
        document.getElementById('request_type_id').addEventListener('change', function() {
            const requestTypeId = this.value;
            const container = document.getElementById('field-defaults-container');

            if (!requestTypeId) {
                container.innerHTML =
                    '<p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i>Select a Request Type above to configure default field values</p>';
                return;
            }

            // Fetch fields for this request type
            fetch(`${APP_BASE_URL}/api/request-types/${requestTypeId}/fields`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    const ct = response.headers.get('content-type') || '';
                    if (!ct.includes('application/json')) return [];
                    return response.json();
                })
                .then(fields => {
                    if (fields.length === 0) {
                        container.innerHTML =
                            '<p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i>This request type has no custom fields</p>';
                        return;
                    }

                    let html =
                        '<small class="text-muted d-block mb-2">Set default values for dynamic fields (optional):</small>';
                    fields.forEach(field => {
                        html += `
                                <div class="form-group mb-2">
                                    <label class="text-sm font-weight-semibold">${field.field_name}</label>
                                    <input type="text" class="form-control form-control-sm" 
                                        name="field_defaults[${field.field_key}]"
                                        placeholder="Default value for ${field.field_name}">
                                </div>
                            `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error('Error fetching fields:', err);
                    container.innerHTML =
                        '<p class="text-danger mb-0"><i class="fas fa-triangle-exclamation mr-1"></i>Failed to load fields</p>';
                });
        });
    </script>
@endsection
