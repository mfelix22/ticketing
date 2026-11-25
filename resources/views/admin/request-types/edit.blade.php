@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('request-types.index') }}">Request Types</a></li>
                    <li class="breadcrumb-item active">Edit Request Type</li>
                </ol>
            </nav>
            <h2 class="text-gray-800 font-semibold text-2xl mb-1">Edit Request Type: {{ $requestType->name }}</h2>
            <p class="text-gray-600 text-sm">Update request type information</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-triangle-exclamation me-2"></i>There were some problems with your input.
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gray-800 font-semibold">Request Type Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('request-types.update', $requestType) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Request Type Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label text-gray-700 font-semibold">
                                    Request Type Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name', $requestType->name) }}" required
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
                                    rows="4" placeholder="Describe when users should choose this request type (optional)">{{ old('description', $requestType->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Request Type
                                </button>
                                <a href="{{ route('request-types.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Dynamic Fields Management Card -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-gray-800 font-semibold">
                                <i class="fas fa-list-alt me-2"></i>Custom Fields
                            </h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addFieldModal">
                                <i class="fas fa-plus me-1"></i>Add Field
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if ($requestType->fields->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="color: #111827;">Order</th>
                                            <th style="color: #111827;">Field Label</th>
                                            <th style="color: #111827;">Field Key</th>
                                            <th style="color: #111827;">Type</th>
                                            <th style="color: #111827;">Required</th>
                                            <th style="color: #111827;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requestType->fields as $field)
                                            <tr>
                                                <td style="color: #111827;">{{ $field->order }}</td>
                                                <td style="color: #111827;">{{ $field->field_name }}</td>
                                                <td style="color: #6b7280;"><code>{{ $field->field_key }}</code></td>
                                                <td>
                                                    <span class="badge bg-secondary"
                                                        style="background-color: #6c757d !important; color: #fff !important;">
                                                        {{ $field->field_type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($field->is_required)
                                                        <span class="badge bg-warning"
                                                            style="background-color: #fd7e14 !important; color: #000 !important;">Required</span>
                                                    @else
                                                        <span class="badge bg-secondary"
                                                            style="background-color: #6c757d !important; color: #fff !important;">Optional</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editFieldModal{{ $field->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form
                                                        action="{{ route('request-types.fields.destroy', [$requestType, $field]) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Delete this field?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-gray-400 mb-3"></i>
                                <p class="text-gray-600">No custom fields yet. Click "Add Field" to create one.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Add Field Modal -->
            <div class="modal fade" id="addFieldModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('request-types.fields.store', $requestType) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" style="color: #111827;">Add Custom Field</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label" style="color: #111827;">Field Label <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="field_name" class="form-control"
                                        placeholder="e.g., Delivery Address" required>
                                    <small class="text-muted">What users will see</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="color: #111827;">Field Key <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="field_key" class="form-control"
                                        placeholder="e.g., delivery_address" required>
                                    <small class="text-muted">No spaces, use underscore (e.g., delivery_address)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="color: #111827;">Field Type <span
                                            class="text-danger">*</span></label>
                                    <select name="field_type" class="form-select" required>
                                        <option value="text">Text (short input)</option>
                                        <option value="textarea">Textarea (long text)</option>
                                        <option value="number">Number</option>
                                        <option value="select">Select (Dropdown)</option>
                                        <option value="date">Date</option>
                                        <option value="email">Email</option>
                                        <option value="tel">Phone Number</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="color: #111827;">Options (for select type)</label>
                                    <input type="text" name="field_options" class="form-control"
                                        placeholder="Morning, Afternoon, Evening">
                                    <small class="text-muted">Comma-separated values (only for select type)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="color: #111827;">Placeholder</label>
                                    <input type="text" name="placeholder" class="form-control"
                                        placeholder="e.g., Enter your address">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" style="color: #111827;">Display Order <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="order" class="form-control"
                                        value="{{ $requestType->fields->count() }}" required min="0">
                                    <small class="text-muted">Lower numbers appear first</small>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_required" value="1" class="form-check-input"
                                            id="is_required">
                                        <label class="form-check-label" for="is_required" style="color: #111827;">
                                            Required Field
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Field</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Sidebar -->
            <div class="col-lg-4">
                <!-- Request Type Stats -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-info bg-opacity-10 border-bottom">
                        <h6 class="mb-0 text-info font-semibold">
                            <i class="fas fa-chart-bar me-2"></i>Request Type Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <div class="text-sm text-gray-600">Total Tickets</div>
                            <div class="text-gray-800 font-semibold text-xl">
                                {{ $requestType->tickets()->count() }}
                            </div>
                        </div>
                        <hr>
                        <div class="mb-2">
                            <div class="text-sm text-gray-600">Created</div>
                            <div class="text-gray-800 font-semibold">
                                {{ $requestType->created_at->format('M d, Y') }}
                            </div>
                        </div>
                        <hr>
                        <div>
                            <div class="text-sm text-gray-600">Last Updated</div>
                            <div class="text-gray-800 font-semibold">
                                {{ $requestType->updated_at->diffForHumans() }}
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
                            Changing the request type name will affect:
                        </p>
                        <ul class="text-sm text-gray-600 mb-0 ps-3">
                            <li>All tickets using this request type</li>
                            <li>Request type visibility in dropdowns</li>
                            <li>Reporting and analytics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Field Modals moved outside table to avoid nested form issues -->
    @foreach ($requestType->fields as $field)
        <div class="modal fade" id="editFieldModal{{ $field->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('request-types.fields.update', [$requestType, $field]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" style="color: #111827;">Edit Field</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" style="color: #111827;">Field Label <span class="text-danger">*</span></label>
                                <input type="text" name="field_name" class="form-control" value="{{ $field->field_name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: #111827;">Field Key <span class="text-danger">*</span></label>
                                <input type="text" name="field_key" class="form-control" value="{{ $field->field_key }}" required>
                                <small class="text-muted">No spaces, use underscore (e.g., delivery_address)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: #111827;">Field Type <span class="text-danger">*</span></label>
                                <select name="field_type" class="form-select" required>
                                    <option value="text" {{ $field->field_type == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="textarea" {{ $field->field_type == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    <option value="number" {{ $field->field_type == 'number' ? 'selected' : '' }}>Number</option>
                                    <option value="select" {{ $field->field_type == 'select' ? 'selected' : '' }}>Select (Dropdown)</option>
                                    <option value="date" {{ $field->field_type == 'date' ? 'selected' : '' }}>Date</option>
                                    <option value="email" {{ $field->field_type == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="tel" {{ $field->field_type == 'tel' ? 'selected' : '' }}>Phone</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: #111827;">Options (for select type)</label>
                                <input type="text" name="field_options" class="form-control" value="{{ is_array($field->field_options) ? implode(', ', $field->field_options) : '' }}" placeholder="Morning, Afternoon, Evening">
                                <small class="text-muted">Comma-separated values</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: #111827;">Placeholder</label>
                                <input type="text" name="placeholder" class="form-control" value="{{ $field->placeholder }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="color: #111827;">Display Order <span class="text-danger">*</span></label>
                                <input type="number" name="order" class="form-control" value="{{ $field->order }}" required min="0">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_required" value="1" class="form-check-input" id="is_required_edit{{ $field->id }}" {{ $field->is_required ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_required_edit{{ $field->id }}" style="color: #111827;">Required Field</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Field</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
