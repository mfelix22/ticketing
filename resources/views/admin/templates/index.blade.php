@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                    <i class="fas fa-layer-group mr-2 text-primary"></i>Ticket Templates
                </h1>
                <p class="text-muted mb-0">Create and manage reusable ticket templates</p>
            </div>
            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>Create Template
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <!-- Templates Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Name</th>
                                    <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Request Type</th>
                                    <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Department</th>
                                    <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Priority</th>
                                    <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Status</th>
                                    <th class="px-4 py-3 text-sm font-semibold" style="color: #111827;">Created By</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-end" style="color: #111827;">Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div>
                                                <div class="font-weight-bold text-gray-800">{{ $template->name }}</div>
                                                @if ($template->description)
                                                    <small
                                                        class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-info text-white">
                                                {{ $template->requestType->name }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($template->department)
                                                <span class="text-sm">{{ $template->department->name }}</span>
                                            @else
                                                <span class="text-muted text-sm">All Departments</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($template->defaultPriority)
                                                @php
                                                    $priorityClass = match ($template->defaultPriority->name) {
                                                        'Urgent' => 'bg-danger text-white',
                                                        'High' => 'bg-warning text-dark',
                                                        'Medium' => 'bg-primary text-white',
                                                        'Low' => 'bg-secondary text-white',
                                                        default => 'bg-secondary text-white',
                                                    };
                                                @endphp
                                                <span class="badge {{ $priorityClass }}">
                                                    {{ $template->defaultPriority->name }}
                                                </span>
                                            @else
                                                <span class="text-muted text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($template->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check mr-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times mr-1"></i>Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $template->creator->name ?? 'System' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <a href="{{ route('admin.templates.edit', $template) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.templates.destroy', $template) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-top">
                        {{ $templates->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-layer-group fa-3x text-gray-300 mb-3"></i>
                        <p class="text-gray-500 mb-3">No templates found</p>
                        <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>Create Your First Template
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
