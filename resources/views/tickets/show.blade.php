<x-app-layout>
    <x-slot name="header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $ticket->request_number }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                    <li class="breadcrumb-item active">{{ $ticket->request_number }}</li>
                </ol>
            </div>
        </div>
    </x-slot>

    <section class="content">
        <div class="container-fluid">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle mr-2"></i><strong>There were some problems with your input:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <!-- Ticket Info Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-ticket-alt mr-2"></i>
                                {{ $ticket->title }}
                            </h3>
                            <div class="card-tools">
                                @php
                                    $statusBadgeClass = match ($ticket->status->name) {
                                        'Open' => 'badge-primary',
                                        'In Progress' => 'badge-warning text-dark',
                                        'Pending Approval' => 'badge-info text-dark',
                                        'Completed' => 'badge-success',
                                        default => 'badge-secondary',
                                    };
                                    $statusStyle = match ($ticket->status->name) {
                                        'Open'
                                            => 'background-color: #007bff !important; color: #fff !important; font-weight: 600;',
                                        'In Progress'
                                            => 'background-color: #fd7e14 !important; color: #000 !important; font-weight: 600;',
                                        'Pending Approval'
                                            => 'background-color: #ffc107 !important; color: #000 !important; font-weight: 600;',
                                        'Completed'
                                            => 'background-color: #28a745 !important; color: #fff !important; font-weight: 600;',
                                        'Closed'
                                            => 'background-color: #6c757d !important; color: #fff !important; font-weight: 600;',
                                        default
                                            => 'background-color: #6c757d !important; color: #fff !important; font-weight: 600;',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadgeClass }} badge-lg" style="{{ $statusStyle }}">
                                    {{ $ticket->status->name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Request Information Card (Dynamic Fields) -->
                    @if (is_array($ticket->detail_request) &&
                            count($ticket->detail_request) > 0 &&
                            $ticket->requestType->fields->count() > 0)
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list-check mr-2"></i>
                                    Request Information
                                </h3>
                                @if (in_array($ticket->requestType->name, ['Delivery', 'Courier']))
                                    <div class="card-tools">
                                        @if ($ticket->receipt_date)
                                            <a href="{{ route('tickets.print-receipt', $ticket) }}"
                                                class="btn btn-primary btn-sm mr-1" target="_blank"
                                                title="Print or download receipt PDF">
                                                <i class="fas fa-file-pdf mr-1"></i>Print Receipt
                                            </a>
                                        @endif
                                        <a href="{{ route('courier.label', $ticket) }}" class="btn btn-success btn-sm"
                                            target="_blank" title="Print delivery label for box">
                                            <i class="fas fa-print mr-1"></i>Print Label
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    @foreach ($ticket->requestType->fields as $field)
                                        @if (isset($ticket->detail_request[$field->field_key]))
                                            <tr>
                                                <td style="width: 200px;"><strong><i
                                                            class="fas fa-check-circle mr-1 text-success"></i>
                                                        {{ $field->field_name }}</strong></td>
                                                <td style="color: #111827;">
                                                    @if (is_array($ticket->detail_request[$field->field_key]))
                                                        {{ implode(', ', $ticket->detail_request[$field->field_key]) }}
                                                    @else
                                                        {{ $ticket->detail_request[$field->field_key] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    @elseif (is_string($ticket->detail_request) && !empty($ticket->detail_request))
                        {{-- Backward compatibility: Show old text-based detail_request for legacy tickets --}}
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-align-left mr-2"></i>
                                    Request Details
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="bg-light p-3 rounded" style="color: #111827;">
                                    {{ $ticket->detail_request }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Attachments Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-paperclip mr-2"></i>
                                Attachments
                            </h3>
                        </div>
                        <div class="card-body">
                            @if ($ticket->attachments->count() > 0)
                                <div class="list-group">
                                    @foreach ($ticket->attachments as $attachment)
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                            target="_blank">
                                            <span>
                                                <i class="fas fa-file mr-2 text-info"></i>
                                                {{ $attachment->file_name }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $attachment->created_at->format('M d, Y H:i') }}
                                            </small>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No attachments</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Comments Card -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-comments mr-2"></i>
                                Comments ({{ $ticket->comments->count() }})
                            </h3>
                        </div>
                        <div class="card-body">
                            @if ($ticket->comments->count() > 0)
                                <div class="timeline">
                                    @foreach ($ticket->comments as $comment)
                                        <div class="time-label">
                                            <span class="bg-blue">{{ $comment->created_at->format('M d, Y') }}</span>
                                        </div>
                                        <div>
                                            <i class="fas fa-comment bg-blue"
                                                style="{{ auth()->id() === $comment->user_id ? 'background-color: #28a745 !important;' : '' }}"></i>
                                            <div class="timeline-item"
                                                style="{{ auth()->id() === $comment->user_id ? 'background-color: #f0fff4; border-left: 3px solid #28a745;' : '' }}">
                                                <span class="time">
                                                    <i class="fas fa-clock"></i>
                                                    {{ $comment->created_at->format('H:i') }}
                                                </span>
                                                <h3 class="timeline-header">
                                                    <strong>{{ $comment->user->name }}</strong>
                                                    @if (auth()->id() === $comment->user_id)
                                                        <span class="badge badge-success ml-2"
                                                            style="background-color: #28a745 !important; font-size: 0.75rem;">You</span>
                                                    @endif
                                                </h3>
                                                <div class="timeline-body"
                                                    style="white-space: pre-wrap; word-wrap: break-word;">
                                                    {{ $comment->comment }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div>
                                        <i class="fas fa-clock bg-gray"></i>
                                    </div>
                                </div>
                            @else
                                <div class="callout callout-info">
                                    <h5><i class="fas fa-info mr-2"></i>No comments yet</h5>
                                    <p>Be the first to add a comment to this ticket.</p>
                                </div>
                            @endif

                            <!-- Add Comment Form -->
                            <div class="mt-3">
                                <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="comment">
                                            <i class="fas fa-plus mr-1"></i>
                                            Add Comment
                                        </label>
                                        <textarea class="form-control" name="comment" id="comment" rows="4" placeholder="Write your comment here..."
                                            required></textarea>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-comment mr-2"></i>Add Comment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Ticket Details Card -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Ticket Details
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong><i class="fas fa-flag mr-1"></i> Status</strong></td>
                                    <td>
                                        <span class="badge {{ $statusBadgeClass }} badge-lg"
                                            style="{{ $statusStyle }}">
                                            {{ $ticket->status->name }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-exclamation-triangle mr-1"></i> Priority</strong></td>
                                    <td>
                                        @php
                                            $priorityBadgeClass = match ($ticket->priority->name) {
                                                'Urgent' => 'badge-danger',
                                                'High' => 'badge-warning',
                                                'Medium' => 'badge-info',
                                                default => 'badge-secondary',
                                            };
                                            $priorityStyle = match ($ticket->priority->name) {
                                                'Urgent'
                                                    => 'background-color: #dc2626 !important; color: #fff !important; font-weight: 600;',
                                                'High'
                                                    => 'background-color: #fd7e14 !important; color: #000 !important; font-weight: 600;',
                                                'Medium'
                                                    => 'background-color: #17a2b8 !important; color: #fff !important; font-weight: 600;',
                                                'Low'
                                                    => 'background-color: #6c757d !important; color: #fff !important; font-weight: 600;',
                                                default
                                                    => 'background-color: #6c757d !important; color: #fff !important; font-weight: 600;',
                                            };
                                        @endphp
                                        <span class="badge {{ $priorityBadgeClass }}" style="{{ $priorityStyle }}">
                                            {{ $ticket->priority->name }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-building mr-1"></i> Department</strong></td>
                                    <td>{{ $ticket->department->name }}</td>
                                </tr>
                                @php
                    $isITDept = $ticket->department->name === 'IT';
                    $isPurchasingDept = $ticket->department->name === 'Purchasing';
                    $isITOrPurchasing = $isITDept || $isPurchasingDept;
                @endphp
                                @if ($ticket->schedule_date)
                                    <tr>
                                        <td><strong><i class="fas fa-calendar-check mr-1"></i> Schedule Date</strong>
                                        </td>
                                        <td>
                                            <span class="d-block text-info fw-semibold">
                                                {{ $ticket->schedule_date->format('M d, Y H:i') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong><i class="fas fa-calendar-alt mr-1"></i> Due Date</strong></td>
                                    <td>
                                        @php
                                            $finishedStatuses = ['Completed', 'Closed', 'Cancelled'];
                                        @endphp
                                        @if (in_array($ticket->status->name, $finishedStatuses))
                                            <span class="text-success fw-bold">Completed</span>
                                        @elseif ($ticket->due_date)
                                            @php
                                                // Check if current time has passed due_date
                                                $isOverdue = now()->greaterThan($ticket->due_date);
                                                $hoursUntilDue = now()->diffInHours($ticket->due_date, false);
                                                $isDueSoon = !$isOverdue && $hoursUntilDue > 0 && $hoursUntilDue <= 24;
                                            @endphp
                                            <div>
                                                <span
                                                    class="d-block {{ $isOverdue ? 'text-danger fw-bold' : ($isDueSoon ? 'text-warning fw-semibold' : '') }}">
                                                    {{ $ticket->due_date->format('M d, Y H:i') }}
                                                </span>
                                                @if ($isOverdue)
                                                    <span class="badge badge-danger mt-1 animate-pulse">
                                                        🔥 OVERDUE - {{ $ticket->due_date->diffForHumans() }}
                                                    </span>
                                                @elseif ($isDueSoon)
                                                    @php
                                                        $hours = floor($hoursUntilDue);
                                                        $minutes = round(($hoursUntilDue - $hours) * 60);
                                                    @endphp
                                                    <span class="badge badge-warning mt-1">
                                                        ⏰ Due in {{ $hours }}h {{ $minutes }}m
                                                    </span>
                                                @else
                                                    <small class="text-muted d-block">
                                                        {{ $ticket->due_date->diffForHumans() }}
                                                    </small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-list mr-1"></i> Type</strong></td>
                                    <td>{{ $ticket->requestType->name }}</td>
                                </tr>
                                @if (isset($ticket->detail_request['package_description']) && $ticket->detail_request['package_description'])
                                    <tr>
                                        <td><strong><i class="fas fa-box mr-1"></i> Package Description</strong></td>
                                        <td style="white-space: pre-wrap; word-wrap: break-word;">{{ $ticket->detail_request['package_description'] }}</td>
                                    </tr>
                                @endif
                                @if ($ticket->receipt_comment)
                                    <tr>
                                        <td><strong><i class="fas fa-comment-dots mr-1"></i> Comment / Reason</strong></td>
                                        <td style="white-space: pre-wrap; word-wrap: break-word;">{{ $ticket->receipt_comment }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong><i class="fas fa-user mr-1"></i> Requester</strong></td>
                                    <td>{{ $ticket->requester->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-building mr-1"></i> Department Requester</strong></td>
                                    <td>{{ $ticket->requester->department->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-calendar mr-1"></i> Created</strong></td>
                                    <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong><i class="fas fa-clock mr-1"></i> Updated</strong></td>
                                    <td>{{ $ticket->updated_at->diffForHumans() }}</td>
                                </tr>
                                @if ($ticket->assigned_to)
                                    <tr>
                                        <td><strong><i class="fas fa-user-check mr-1"></i> Assigned To</strong></td>
                                        <td>{{ $ticket->assignedTo?->name ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                                @if ($ticket->receipt_user_id)
                                    <tr>
                                        <td><strong><i class="fas fa-user-cog mr-1"></i> Working By (PIC)</strong></td>
                                        <td>{{ $ticket->receiptUser?->name ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                                @if ($ticket->acceptance_user_id)
                                    <tr>
                                        <td><strong><i class="fas fa-user-check mr-1"></i> Accepted By</strong></td>
                                        <td>{{ $ticket->acceptanceUser?->name ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                                {{-- @if ($ticket->finished_date)
                                    <tr>
                                        <td><strong><i class="fas fa-calendar-check mr-1"></i> Finished Date</strong>
                                        </td>
                                        <td>{{ $ticket->finished_date->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endif --}}
                            </table>
                        </div>
                    </div>

                    <!-- Manager & GM Approval Card (IT Tickets Only) -->
                    @if ($isITDept)
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    Manager & GM Approval
                                </h3>
                            </div>
                            <div class="card-body">
                                @if ($ticket->requires_manager_approval)
                                    <div class="alert {{ $ticket->manager_approved ? 'alert-success' : 'alert-info' }}">
                                        <i class="fas {{ $ticket->manager_approved ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                        @if ($ticket->manager_approved)
                                            <strong>Manager Approved</strong> by {{ $ticket->managerApprovedByUser?->name ?? 'N/A' }}
                                            <br>
                                            <small>{{ $ticket->manager_approved_at?->format('M d, Y H:i') ?? '' }}</small>
                                        @else
                                            <strong>Pending Manager Approval</strong>
                                            <br>
                                            Waiting for: {{ $ticket->managerApprovalUser?->name ?? 'N/A' }}
                                        @endif
                                    </div>

                                    @if (!$ticket->manager_approved && auth()->user()->id === $ticket->manager_approval_user_id)
                                        <form action="{{ route('tickets.approve-manager', $ticket) }}" method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-check mr-2"></i>Approve as Manager
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if ($ticket->requires_gm_approval)
                                    <div class="alert {{ $ticket->gm_approved ? 'alert-success' : 'alert-info' }}">
                                        <i class="fas {{ $ticket->gm_approved ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                        @if ($ticket->gm_approved)
                                            <strong>GM Approved</strong> by {{ $ticket->gmApprovedByUser?->name ?? 'N/A' }}
                                            <br>
                                            <small>{{ $ticket->gm_approved_at?->format('M d, Y H:i') ?? '' }}</small>
                                        @else
                                            <strong>Pending GM Approval</strong>
                                            <br>
                                            Waiting for: {{ $ticket->gmApprovalUser?->name ?? 'N/A' }}
                                        @endif
                                    </div>

                                    @if ($ticket->manager_approved && !$ticket->gm_approved && auth()->user()->id === $ticket->gm_approval_user_id)
                                        <form action="{{ route('tickets.approve-gm', $ticket) }}" method="POST" class="mb-3">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-check mr-2"></i>Approve as GM
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if (auth()->user()->id !== $ticket->manager_approval_user_id && auth()->user()->id !== $ticket->gm_approval_user_id && !$ticket->manager_approved && !$ticket->gm_approved)
                                    <form action="{{ route('tickets.manager-approval', $ticket) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                    class="custom-control-input"
                                                    id="requires_manager_approval"
                                                    name="requires_manager_approval"
                                                    value="1"
                                                    {{ old('requires_manager_approval', $ticket->requires_manager_approval ? '1' : '') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="requires_manager_approval">
                                                    <strong>Requires Manager Approval</strong>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group" id="manager_approval_select_group"
                                            style="{{ old('requires_manager_approval', $ticket->requires_manager_approval ? '1' : '') == '1' ? '' : 'display: none;' }}">
                                            <label for="manager_approval_user_id">
                                                <i class="fas fa-user-tie mr-1"></i>
                                                Select Manager <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                class="form-control @error('manager_approval_user_id') is-invalid @enderror"
                                                name="manager_approval_user_id"
                                                id="manager_approval_user_id">
                                                <option value="">-- Select Manager --</option>
                                                @foreach ($managers as $manager)
                                                    <option value="{{ $manager->id }}"
                                                        {{ old('manager_approval_user_id', $ticket->manager_approval_user_id) == $manager->id ? 'selected' : '' }}>
                                                        {{ $manager->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('manager_approval_user_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                    class="custom-control-input"
                                                    id="requires_gm_approval"
                                                    name="requires_gm_approval"
                                                    value="1"
                                                    {{ old('requires_gm_approval', $ticket->requires_gm_approval ? '1' : '') == '1' ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="requires_gm_approval">
                                                    <strong>Requires GM Approval (Optional)</strong>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group" id="gm_approval_select_group"
                                            style="{{ old('requires_gm_approval', $ticket->requires_gm_approval ? '1' : '') == '1' ? '' : 'display: none;' }}">
                                            <label for="gm_approval_user_id">
                                                <i class="fas fa-user-tie mr-1"></i>
                                                Select GM <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                class="form-control @error('gm_approval_user_id') is-invalid @enderror"
                                                name="gm_approval_user_id"
                                                id="gm_approval_user_id">
                                                <option value="">-- Select GM --</option>
                                                @foreach ($generalManagers as $gm)
                                                    <option value="{{ $gm->id }}"
                                                        {{ old('gm_approval_user_id', $ticket->gm_approval_user_id) == $gm->id ? 'selected' : '' }}>
                                                        {{ $gm->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('gm_approval_user_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <br>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save mr-2"></i>Save Approval Settings
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Completion Details Card (Show when ticket has been marked as completed) -->
                    @if ($ticket->finished_date && in_array($ticket->status->name, ['Pending Approval', 'Completed', 'Closed']))
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-list mr-2"></i>
                                    Completion Details
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm">
                                    {{-- @if ($ticket->note)
                                        <tr>
                                            <td colspan="2">
                                                <strong><i class="fas fa-sticky-note mr-1"></i> Note:</strong><br>
                                                <small class="text-muted">{{ $ticket->note }}</small>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($ticket->expenses_value)
                                        <tr>
                                            <td><strong><i class="fas fa-money-bill-wave mr-1"></i> Expenses</strong>
                                            </td>
                                            <td>Rp {{ number_format($ticket->expenses_value, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if ($ticket->usage_hours)
                                        <tr>
                                            <td><strong><i class="fas fa-clock mr-1"></i> Usage Hours</strong></td>
                                            <td>{{ $ticket->usage_hours }} hours</td>
                                        </tr>
                                    @endif
                                    @if ($ticket->payment_flag)
                                        <tr>
                                            <td><strong><i class="fas fa-credit-card mr-1"></i> Payment Method</strong>
                                            </td>
                                            <td>
                                                @if ($ticket->payment_flag === 'Bank')
                                                    <span class="badge badge-primary"
                                                        style="background-color: #007bff !important; color: #fff !important; font-weight: 600;">
                                                        <i class="fas fa-university mr-1"></i>Bank Transfer
                                                    </span>
                                                @elseif ($ticket->payment_flag === 'Cash')
                                                    <span class="badge badge-success"
                                                        style="background-color: #28a745 !important; color: #fff !important; font-weight: 600;">
                                                        <i class="fas fa-money-bill-wave mr-1"></i>Cash
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge badge-secondary">{{ $ticket->payment_flag }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif --}}
                                    @if ($ticket->handling_user)
                                        <tr>
                                            <td><strong><i class="fas fa-user-cog mr-1"></i> Handled By</strong></td>
                                            <td>{{ $ticket->handling_user }}</td>
                                        </tr>
                                    @endif
                                    @if ($ticket->finished_date)
                                        <tr>
                                            <td><strong><i class="fas fa-calendar-check mr-1"></i> Finished
                                                    Date</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($ticket->finished_date)->format('M d, Y H:i') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($ticket->additional_note)
                                        <tr>
                                            <td colspan="2"><strong><i class="fas fa-comment-alt mr-1"></i>
                                                    Additional Notes</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="white-space: pre-wrap; word-wrap: break-word;">
                                                {{ $ticket->additional_note }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Ticket History Card - Removed, will be shown in reports only --}}

                    <!-- Receipt/Approval Card (Only for Assigned Approver or Approvers in Department) -->
                    @php
                        $currentUser = auth()->user();
                        $isApprover = in_array($currentUser->status, ['Approver', 'Both']);

                        // Check if user can approve this ticket
                        $canApprove = false;
                        if ($isApprover && !$ticket->receipt_date) {
                            // IT tickets with pending manager approval cannot be receipted yet
                            $managerApprovalPending = $isITDept && (
                                ($ticket->requires_manager_approval && !$ticket->manager_approved) ||
                                ($ticket->requires_gm_approval && !$ticket->gm_approved)
                            );

                            if ($managerApprovalPending) {
                                $canApprove = false;
                            } elseif ($ticket->assigned_to) {
                                // If ticket is assigned to specific person, ONLY they can approve
                                $canApprove = $ticket->assigned_to === $currentUser->id;
                            } else {
                                // If no specific assignment, all approvers can approve
                                $canApprove = true;
                            }
                        }
                    @endphp

                    @if ($canApprove)
                        <div class="card card-info card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check mr-2"></i>
                                    Receipt & Approval
                                </h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('tickets.receipt', $ticket) }}" method="POST">
                                    @csrf

                                    @if (!$isITOrPurchasing)
                                        <div class="form-group">
                                            <label for="handling_user">
                                                <i class="fas fa-user-cog mr-1"></i>
                                                @if ($ticket->requestType->requires_external_handling)
                                                    External Handler / Courier Name <span class="text-danger">*</span>
                                                @else
                                                    Assign to User <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            @if ($ticket->requestType->requires_external_handling)
                                                {{-- Text input for external/courier with autocomplete --}}
                                                <input type="text"
                                                    class="form-control @error('handling_user') is-invalid @enderror"
                                                    name="handling_user" id="handling_user" list="courier_names"
                                                    placeholder="Enter or select courier name from history">
                                                <datalist id="courier_names">
                                                    @php
                                                        $previousCouriers = \App\Models\Ticket::whereNotNull(
                                                            'handling_user',
                                                        )
                                                            ->whereHas('requestType', function ($q) {
                                                                $q->where('name', 'Delivery')->orWhere(
                                                                    'name',
                                                                    'Courier',
                                                                );
                                                            })
                                                            ->select('handling_user')
                                                            ->distinct()
                                                            ->orderBy('handling_user')
                                                            ->pluck('handling_user');
                                                    @endphp
                                                    @foreach ($previousCouriers as $courierName)
                                                        <option value="{{ $courierName }}">
                                                    @endforeach
                                                </datalist>
                                                <small class="form-text text-muted">
                                                    Type to select from previous courier names or enter a new one
                                                </small>
                                            @else
                                                {{-- Dropdown for internal users --}}
                                                <select
                                                    class="form-control @error('handling_user_id') is-invalid @enderror"
                                                    name="handling_user_id" id="handling_user_id">
                                                    <option value="">-- Select User --</option>
                                                    @foreach (\App\Models\User::whereIn('status', ['Approver', 'Both', 'PIC'])->orderBy('name')->get() as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                            ({{ $user->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    Select the user who will handle and complete this request (only
                                                    Approvers, PICs, and Both can handle tickets)
                                                </small>
                                            @endif @error('handling_user')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        @error('handling_user_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif {{-- end @if (!$isITOrPurchasing) for assign to user --}}

                                @if (!$isITOrPurchasing)
                                    <div class="form-group">
                                        <label for="schedule_date">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            Schedule Date <span class="text-danger">*</span>
                                        </label>
                                        @if (in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                            <input type="text" class="form-control" value="Completed"
                                                disabled>
                                            <small class="form-text text-success">This ticket is completed.
                                                Schedule
                                                date
                                                cannot be changed.</small>
                                        @else
                                            <input type="datetime-local"
                                                class="form-control @error('schedule_date') is-invalid @enderror"
                                                name="schedule_date" id="schedule_date"
                                                value="{{ old('schedule_date', $ticket->schedule_date ? $ticket->schedule_date->format('Y-m-d\TH:i') : '') }}"
                                                placeholder="Select date and time">
                                            <small class="form-text text-muted">
                                                Current:
                                                {{ $ticket->schedule_date ? $ticket->schedule_date->format('M d, Y H:i') : 'Not set' }}
                                            </small>
                                            @error('schedule_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        @endif
                                    </div>
                                @endif {{-- end @if (!$isITOrPurchasing) for schedule_date --}}

                                <div class="form-group">
                                    <label for="receipt_comment">
                                        <i class="fas fa-comment-dots mr-1"></i>
                                        Receipt Comment / Reason <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('receipt_comment') is-invalid @enderror" name="receipt_comment"
                                        id="receipt_comment" rows="4" placeholder="Enter your receipt comment or reason for approval/rejection..."
                                        required></textarea>
                                    @error('receipt_comment')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-question-circle mr-1"></i>
                                        Continue with this ticket? <span class="text-danger">*</span>
                                    </label>
                                    <div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="continue_yes" name="is_continued"
                                                value="1" class="custom-control-input" required>
                                            <label class="custom-control-label text-success" for="continue_yes">
                                                <strong>Yes</strong> - Approve & Continue
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" id="continue_no" name="is_continued"
                                                value="0" class="custom-control-input" required>
                                            <label class="custom-control-label text-danger" for="continue_no">
                                                <strong>No</strong> - Reject
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-check-double mr-2"></i>Submit Receipt
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Manager/GM Approval Pending Notice -->
                @if ($isApprover && !$ticket->receipt_date && $isITDept)
                    @php
                        $approvalPending = null;
                        $approvalUserName = null;
                        $approvalLabel = null;

                        if ($ticket->requires_manager_approval && !$ticket->manager_approved) {
                            $approvalPending = 'Manager';
                            $approvalUserName = $ticket->managerApprovalUser?->name ?? 'the assigned manager';
                            $approvalLabel = 'Manager';
                        } elseif ($ticket->requires_gm_approval && !$ticket->gm_approved) {
                            $approvalPending = 'GM';
                            $approvalUserName = $ticket->gmApprovalUser?->name ?? 'the assigned GM';
                            $approvalLabel = 'GM';
                        }
                    @endphp

                    @if ($approvalPending)
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    {{ $approvalLabel }} Approval Required
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-0">
                                    <h5><i class="fas fa-clock mr-2"></i>Waiting for {{ $approvalLabel }} Approval</h5>
                                    <p class="mb-2">
                                        This ticket is waiting for approval from
                                        <strong>{{ $approvalUserName }}</strong>.
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Receipt submission is disabled until the {{ strtolower($approvalLabel) }} approves.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Print Receipt Card (After Receipt Submitted) -->
                @if ($ticket->receipt_date && ($isApprover || $currentUser->status === 'Super Admin'))
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Receipt Document
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                Receipt has been submitted and saved. You can print or download the receipt PDF
                                anytime.
                            </p>
                            <a href="{{ route('tickets.print-receipt', $ticket) }}"
                                class="btn btn-primary btn-block" target="_blank">
                                <i class="fas fa-file-pdf mr-2"></i>Print / Download Receipt PDF
                            </a>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Receipt includes ticket details, delivery information, approval data, and signature
                                boxes.
                            </small>
                        </div>
                    </div>
                @endif

                <!-- Message for Approvers Who Can't Approve (Ticket Assigned to Someone Else) -->
                @if ($isApprover && !$ticket->receipt_date && $ticket->assigned_to && $ticket->assigned_to !== $currentUser->id)
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Assignment Notice
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <h5><i class="fas fa-user-lock mr-2"></i>Ticket Assigned to Specific User</h5>
                                <p class="mb-2">This ticket has been assigned to
                                    <strong>{{ $ticket->assignedTo?->name ?? 'Unknown' }}</strong>.
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Only the assigned person can approve or reject this ticket. You can view and
                                    comment, but cannot process the receipt.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Show Receipt Info if Already Submitted -->
                @if ($ticket->receipt_date)
                    <div class="card card-{{ $ticket->is_continued ? 'success' : 'danger' }} card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-receipt mr-2"></i>
                                Receipt Information
                            </h3>
                        </div>
                        <div class="card-body" style="text-align: left;">
                            <div class="mb-3">
                                <strong><i class="fas fa-user mr-1"></i> Recipient User:</strong><br>
                                {{ $ticket->receiptUser?->name ?? 'N/A' }}
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-calendar mr-1"></i> Receipt Date:</strong><br>
                                {{ $ticket->receipt_date->format('M d, Y H:i') }}
                            </div>
                            <div class="mb-3">
                                <strong><i class="fas fa-comment mr-1"></i> Comment / Reason:</strong><br>
                                <div class="p-2 rounded mt-1"
                                    style="background-color: #f8f9fa; white-space: pre-wrap; word-wrap: break-word; text-align: left !important; display: block;">
                                    {{ $ticket->receipt_comment }}</div>
                            </div>
                            <div>
                                <strong><i
                                        class="fas fa-{{ $ticket->is_continued ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                    Status:</strong><br>
                                @if ($ticket->is_continued)
                                    <span class="badge badge-success badge-lg"
                                        style="background-color: #28a745 !important; color: #fff !important; font-weight: 600;">
                                        <i class="fas fa-thumbs-up mr-1"></i>Approved - Continue
                                    </span>
                                @else
                                    <span class="badge badge-danger badge-lg"
                                        style="background-color: #dc3545 !important; color: #fff !important; font-weight: 600;">
                                        <i class="fas fa-thumbs-down mr-1"></i>Rejected - Do Not Continue
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions Card -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools mr-2"></i>
                            Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Mark as Completed Form -->
                        @php
                            $canComplete = false;
                            $completionMessage = '';

                            if ($ticket->status->name === 'In Progress') {
                                $isBillingCollection = $ticket->department && (
                                    $ticket->department->name === 'Billing Collection' ||
                                    $ticket->department_id == 26
                                );

                                if ($isBillingCollection) {
                                    $allowedUsernames = ['sendy', 'lisa'];
                                    $currentUser = auth()->user();
                                    $isAuthorized = in_array(strtolower($currentUser->username ?? ''), $allowedUsernames);

                                    if ($isAuthorized && (!$currentUser->department_id || $currentUser->department_id == $ticket->department_id)) {
                                        $canComplete = true;
                                        $completionMessage = 'As Sendy/Lisa (Billing Collection), you can mark this request as completed.';
                                    } else {
                                        $completionMessage = 'Only Sendy or Lisa can mark tickets in Billing Collection as completed.';
                                    }
                                } else {
                                    if ($ticket->requestType->requires_external_handling) {
                                        // External handling - only PIC can complete
                                        if ($ticket->receipt_user_id === auth()->user()->id) {
                                            $canComplete = true;
                                            $completionMessage =
                                                'As the PIC, you can mark this external request as completed.';
                                        } else {
                                            $completionMessage =
                                                'Only the PIC (' .
                                                ($ticket->receiptUser?->name ?? 'approver') .
                                                ') can mark this external request as completed.';
                                        }
                                    } else {
                                        // Internal handling - only assigned handling user can complete
                                        if ($ticket->handling_user_id === auth()->user()->id) {
                                            $canComplete = true;
                                            $completionMessage = 'You are assigned to complete this request.';
                                        } else {
                                            $completionMessage =
                                                'Only the assigned handling user (' .
                                                ($ticket->handlingUser?->name ?? 'assigned user') .
                                                ') can mark this as completed.';
                                        }
                                    }
                                }
                            }
                        @endphp

                        @if ($canComplete)
                            <div class="card card-success card-outline mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Mark Work as Completed
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        {{ $completionMessage }}
                                    </div>
                                    <form action="{{ route('tickets.complete', $ticket) }}" method="POST">
                                        @csrf

                                        @if (in_array($ticket->requestType->name, ['Delivery', 'Courier']))
                                            <div class="form-group">
                                                <label for="change_handling_user">
                                                    <i class="fas fa-exchange-alt mr-1"></i>
                                                    Change Courier/Expedition Name <small
                                                        class="text-muted">(Optional)</small>
                                                </label>
                                                <input type="text"
                                                    class="form-control @error('change_handling_user') is-invalid @enderror"
                                                    name="change_handling_user" id="change_handling_user"
                                                    list="courier_names_change"
                                                    value="{{ $ticket->handling_user }}"
                                                    placeholder="Change courier/expedition name if needed">
                                                <datalist id="courier_names_change">
                                                    @php
                                                        $previousCouriers = \App\Models\Ticket::whereNotNull(
                                                            'handling_user',
                                                        )
                                                            ->whereHas('requestType', function ($q) {
                                                                $q->where('name', 'Delivery')->orWhere(
                                                                    'name',
                                                                    'Courier',
                                                                );
                                                            })
                                                            ->select('handling_user')
                                                            ->distinct()
                                                            ->orderBy('handling_user')
                                                            ->pluck('handling_user');
                                                    @endphp
                                                    @foreach ($previousCouriers as $courierName)
                                                        <option value="{{ $courierName }}">
                                                    @endforeach
                                                </datalist>
                                                <small class="form-text text-muted">
                                                    Update the courier/expedition name if it changed during delivery
                                                    (e.g., vehicle breakdown)
                                                </small>
                                                @error('change_handling_user')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="form-group">
                                            <label for="note">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                Completion Note <span class="text-danger">*</span>
                                            </label>
                                            @php
                                                $defaultNote = '';
                                                if (in_array($ticket->requestType->name, ['Delivery', 'Courier'])) {
                                                    $defaultNote = "- Biaya Parkir: \n- Biaya Luar Kota: ";
                                                }
                                            @endphp
                                            <textarea class="form-control @error('note') is-invalid @enderror" name="note" id="note" rows="4"
                                                placeholder="Describe what work was completed and any important details..." required>{{ old('note', $defaultNote) }}</textarea>
                                            <small class="form-text text-muted">
                                                Explain what you did to resolve this ticket.
                                            </small>
                                            <td>
                                                @if (in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif ($ticket->due_date)
                                                    @php
                                                        $isOverdue =
                                                            $ticket->due_date->isPast() &&
                                                            !in_array($ticket->status->name, [
                                                                'Completed',
                                                                'Closed',
                                                                'Cancelled',
                                                            ]);
                                                        $hoursUntilDue = now()->diffInHours($ticket->due_date, false);
                                                        $isDueSoon =
                                                            !$isOverdue && $hoursUntilDue > 0 && $hoursUntilDue <= 24;
                                                    @endphp
                                                    <div>
                                                        <span
                                                            class="d-block {{ $isOverdue ? 'text-danger fw-bold' : ($isDueSoon ? 'text-warning fw-semibold' : '') }}">
                                                            {{ $ticket->due_date->format('M d, Y H:i') }}
                                                        </span>
                                                        @if ($isOverdue)
                                                            <span class="badge badge-danger mt-1 animate-pulse">
                                                                🔥 OVERDUE -
                                                                {{ $ticket->due_date->diffForHumans() }}
                                                            </span>
                                                        @elseif ($isDueSoon)
                                                            @php
                                                                $hours = floor($hoursUntilDue);
                                                                $minutes = round(($hoursUntilDue - $hours) * 60);
                                                            @endphp
                                                            <span class="badge badge-warning mt-1">
                                                                ⏰ Due in {{ $hours }}h {{ $minutes }}m
                                                            </span>
                                                        @else
                                                            <span class="badge badge-info mt-1">
                                                                {{ $ticket->due_date->diffForHumans() }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                        </div>

                                        <div class="form-group">
                                            <label for="additional_note">
                                                <i class="fas fa-comment-alt mr-1"></i>
                                                Additional Note <small class="text-muted">(Optional)</small>
                                            </label>
                                            <textarea class="form-control @error('additional_note') is-invalid @enderror" name="additional_note"
                                                id="additional_note" rows="3" placeholder="Add any additional notes or information...">{{ old('additional_note') }}</textarea>
                                            <small class="form-text text-muted">
                                                Additional information or remarks for this completion
                                            </small>
                                            @error('additional_note')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        @if (!$isITOrPurchasing)
                                            <div class="form-group">
                                                <label for="expenses_value">
                                                    <i class="fas fa-money-bill-wave mr-1"></i>
                                                    Expenses Value <small class="text-muted">(Optional)</small>
                                                </label>
                                                <input type="number" step="0.01" min="0"
                                                    name="expenses_value" id="expenses_value"
                                                    class="form-control @error('expenses_value') is-invalid @enderror"
                                                    placeholder="Enter expenses amount"
                                                    value="{{ old('expenses_value') }}">
                                                <small class="form-text text-muted">
                                                    Enter any expenses incurred (leave blank if none)
                                                </small>
                                                @error('expenses_value')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="other_costs">
                                                    <i class="fas fa-receipt mr-1"></i>
                                                    Harga Lain-Lain (Other Costs) <small
                                                        class="text-muted">(Optional)</small>
                                                </label>
                                                <input type="number" step="0.01" min="0"
                                                    name="other_costs" id="other_costs"
                                                    class="form-control @error('other_costs') is-invalid @enderror"
                                                    placeholder="Enter other miscellaneous costs"
                                                    value="{{ old('other_costs') }}">
                                                <small class="form-text text-muted">
                                                    Miscellaneous costs (biaya lain-lain) for weekly/monthly
                                                    tracking
                                                </small>
                                                @error('other_costs')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="usage_hours">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Usage Hours <small class="text-muted">(Optional)</small>
                                                </label>
                                                <input type="number" step="0.1" min="0"
                                                    name="usage_hours" id="usage_hours"
                                                    class="form-control @error('usage_hours') is-invalid @enderror"
                                                    placeholder="Enter hours spent"
                                                    value="{{ old('usage_hours') }}">
                                                <small class="form-text text-muted">
                                                    Enter hours spent on this task (leave blank if not applicable)
                                                </small>
                                                @error('usage_hours')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <label for="payment_flag">
                                                            <i class="fas fa-credit-card mr-1"></i>
                                                            Payment Method <small
                                                                class="text-muted">(Optional)</small>
                                                        </label>
                                                        <select
                                                            class="form-control @error('payment_flag') is-invalid @enderror"
                                                            name="payment_flag" id="payment_flag">
                                                            <option value="">-- Select Payment Method --
                                                            </option>
                                                            <option value="Bank">🏦 Bank Transfer</option>
                                                            <option value="Cash">💵 Cash</option>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                            Only required if this request involves external expenses
                                                        </small>
                                                        @error('payment_flag')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endif {{-- end @if (!$isITOrPurchasing) for expenses section --}}<br>

                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-check-double mr-2"></i>Mark as Completed
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif ($ticket->status->name === 'In Progress')
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                {{ $completionMessage }}
                            </div>
                        @endif

                        <!-- Requester Verification (for requester when status is Pending Approval) -->
                        @if (config('app.requester_verification_enabled', true) && $ticket->requester_id === auth()->user()->id && $ticket->status->name === 'Pending Approval')
                            <div class="card card-warning card-outline mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clipboard-check mr-2"></i>
                                        Work Completed - Please Verify
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <!-- Completion Details -->
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle mr-2"></i>Completion Details:</h5>

                                        {{-- @if ($ticket->note)
                                            <div class="mb-3">
                                                <strong><i class="fas fa-sticky-note mr-1"></i> Note:</strong>
                                                <p class="mb-0 ml-3">{{ $ticket->note }}</p>
                                            </div>
                                        @endif --}}

                                        <div class="row">
                                            {{-- @if ($ticket->expenses_value)
                                                <div class="col-md-6 mb-2">
                                                    <strong><i class="fas fa-money-bill-wave mr-1"></i> Expenses
                                                        Value:</strong><br>
                                                    <span class="ml-3">Rp
                                                        {{ number_format($ticket->expenses_value, 2) }}</span>
                                                </div>
                                            @endif --}}

                                            {{-- @if ($ticket->usage_hours)
                                                <div class="col-md-6 mb-2">
                                                    <strong><i class="fas fa-clock mr-1"></i> Usage
                                                        Hours:</strong><br>
                                                    <span class="ml-3">{{ $ticket->usage_hours }} hours</span>
                                                </div>
                                            @endif --}}

                                            {{-- @if ($ticket->payment_flag)
                                                <div class="col-md-6 mb-2">
                                                    <strong><i class="fas fa-credit-card mr-1"></i> Payment
                                                        Method:</strong><br>
                                                    <span class="ml-3">
                                                        @if ($ticket->payment_flag === 'Bank')
                                                            <span class="badge badge-primary"
                                                                style="background-color: #007bff !important; color: #fff !important; font-weight: 600;">
                                                                <i class="fas fa-university mr-1"></i>Bank Transfer
                                                            </span>
                                                        @elseif ($ticket->payment_flag === 'Cash')
                                                            <span class="badge badge-success"
                                                                style="background-color: #28a745 !important; color: #fff !important; font-weight: 600;">
                                                                <i class="fas fa-money-bill-wave mr-1"></i>Cash
                                                            </span>
                                                        @else
                                                            <span
                                                                class="badge badge-secondary">{{ $ticket->payment_flag }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif --}}

                                            @if ($ticket->handling_user)
                                                <div class="col-md-6 mb-2">
                                                    <strong><i class="fas fa-user-cog mr-1"></i> Handled
                                                        By:</strong><br>
                                                    <span class="ml-3">{{ $ticket->handling_user }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if ($ticket->finished_date)
                                            <div class="mt-2">
                                                <strong><i class="fas fa-calendar-check mr-1"></i> Finished
                                                    Date:</strong>
                                                <span
                                                    class="ml-2">{{ $ticket->finished_date->format('M d, Y H:i') }}</span>
                                            </div>
                                        @endif

                                        @if ($ticket->additional_note)
                                            <div class="mt-3 p-2 rounded"
                                                style="background-color: #f8f9fa; border-left: 3px solid #17a2b8;">
                                                <strong><i class="fas fa-comment-alt mr-1"></i> Additional
                                                    Notes:</strong>
                                                <p class="mb-0 mt-2"
                                                    style="white-space: pre-wrap; word-wrap: break-word; text-align: left !important; display: block;">
                                                    {{ $ticket->additional_note }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="text-muted mb-3">
                                        The handler has marked this ticket as completed. Please review the work and
                                        choose an action:
                                    </p>

                                    <!-- Verify & Close Form -->
                                    <form action="{{ route('tickets.close', $ticket) }}" method="POST"
                                        class="mb-3">
                                        @csrf
                                        <div class="form-group">
                                            <label for="verification_note">
                                                <i class="fas fa-comment mr-1"></i>
                                                Verification Note (Optional)
                                            </label>
                                            <textarea class="form-control" name="verification_note" id="verification_note" rows="3"
                                                placeholder="Any additional feedback or comments (optional)..."></textarea>
                                        </div>
                                        <br>
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-check-circle mr-2"></i>Verify & Close Ticket
                                        </button>
                                    </form>

                                    @if (!in_array($ticket->requestType->name, ['Delivery', 'Courier']))
                                        <!-- OR Divider -->
                                        <div class="text-center my-3">
                                            <span class="badge badge-secondary">OR</span>
                                        </div>

                                        <!-- Reopen Form -->
                                        <form action="{{ route('tickets.reopen', $ticket) }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label for="reopen_reason">
                                                    <i class="fas fa-undo mr-1"></i>
                                                    Reason for Reopening <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control @error('reopen_reason') is-invalid @enderror" name="reopen_reason" id="reopen_reason"
                                                    rows="3" placeholder="Explain what still needs to be done or what's wrong..." required></textarea>
                                                @error('reopen_reason')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <br>
                                            <button type="submit" class="btn btn-warning btn-block">
                                                <i class="fas fa-redo mr-2"></i>Reopen Ticket
                                            </button>
                                        </form>
                                    @else
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <strong>Note:</strong> Delivery requests cannot be reopened. If there's
                                            an issue, please create a new ticket.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- IT Completed Reopen (for requester when IT ticket is Completed) -->
                        @if ($ticket->requester_id === auth()->user()->id && $ticket->status->name === 'Completed' && $ticket->department->name === 'IT')
                            <div class="card card-warning card-outline mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clipboard-check mr-2"></i>
                                        Work Completed — Review Result
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle mr-2"></i>Completion Details:</h5>
                                        @if ($ticket->handling_user)
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <strong><i class="fas fa-user-cog mr-1"></i> Handled By:</strong><br>
                                                    <span class="ml-3">{{ $ticket->handling_user }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if ($ticket->finished_date)
                                            <div class="mt-2">
                                                <strong><i class="fas fa-calendar-check mr-1"></i> Finished Date:</strong>
                                                <span class="ml-2">{{ $ticket->finished_date->format('M d, Y H:i') }}</span>
                                            </div>
                                        @endif
                                        @if ($ticket->additional_note)
                                            <div class="mt-3 p-2 rounded" style="background-color: #f8f9fa; border-left: 3px solid #17a2b8;">
                                                <strong><i class="fas fa-comment-alt mr-1"></i> Additional Notes:</strong>
                                                <p class="mb-0 mt-2" style="white-space: pre-wrap; word-wrap: break-word; text-align: left !important; display: block;">{{ $ticket->additional_note }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="text-muted mb-3">
                                        If the result is unsatisfactory, you can reopen this ticket to request further work.
                                    </p>

                                    <!-- Reopen Form -->
                                    <form action="{{ route('tickets.reopen', $ticket) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="reopen_reason_completed">
                                                <i class="fas fa-undo mr-1"></i>
                                                Reason for Reopening <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('reopen_reason') is-invalid @enderror" name="reopen_reason" id="reopen_reason_completed"
                                                rows="3" placeholder="Explain what still needs to be done or what's wrong..." required></textarea>
                                            @error('reopen_reason')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <br>
                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-redo mr-2"></i>Reopen Ticket
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        <!-- Show current handler if accepted -->
                        @if ($ticket->acceptance_user_id && auth()->user()->canManageTicket($ticket))
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Receipt by:</strong> {{ $ticket->acceptanceUser?->name ?? 'N/A' }}
                            </div>
                        @endif

                        <!-- Ticket Locked Message -->
                        @if (!in_array($ticket->status->name, ['Open', 'In Progress', 'Pending Approval']))
                            <div class="alert alert-secondary mb-3">
                                <i class="fas fa-lock mr-2"></i>
                                <strong>This ticket is locked.</strong> Editing is disabled for
                                {{ $ticket->status->name }} tickets.
                            </div>
                        @endif

                        <!-- Horizontal Action Buttons -->
                        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                            @php
                                $editableStatuses = ['Open', 'In Progress', 'Pending Approval'];
                            @endphp

                            <!-- Delete Button removed: users must cancel via workflow -->

                            <!-- Back Button -->
                            <a href="{{ route('tickets.index') }}" class="btn btn-secondary flex-fill"
                                style="min-width: 80px;">
                                <i class="fas fa-arrow-left mr-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Initialize Flatpickr for due_date with date AND time picker
    document.addEventListener('DOMContentLoaded', function() {
        const dueDateInput = document.getElementById('due_date');

        if (dueDateInput) {
            flatpickr(dueDateInput, {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: false,
                altInput: true,
                altFormat: "M d, Y h:i K",
                minuteIncrement: 1,
                defaultDate: dueDateInput.value ? dueDateInput.value.replace('T', ' ') : null,
                onChange: function(selectedDates, dateStr, instance) {
                    // Convert to datetime-local format for form submission
                    if (selectedDates[0]) {
                        const date = selectedDates[0];
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        dueDateInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                    }
                }
            });
        }

        // Toggle required fields based on is_continued radio selection
        const continueYes = document.getElementById('continue_yes');
        const continueNo = document.getElementById('continue_no');
        const handlingUserInput = document.getElementById('handling_user');
        const handlingUserIdInput = document.getElementById('handling_user_id');
        const dueDateField = document.getElementById('due_date');

        function toggleRequiredFields() {
            const isContinued = continueYes && continueYes.checked;

            // Toggle required attribute
            if (handlingUserInput) {
                handlingUserInput.required = isContinued;
            }
            if (handlingUserIdInput) {
                handlingUserIdInput.required = isContinued;
            }
            if (dueDateField) {
                dueDateField.required = isContinued;
            }
        }

        // Add event listeners
        if (continueYes) {
            continueYes.addEventListener('change', toggleRequiredFields);
        }
        if (continueNo) {
            continueNo.addEventListener('change', toggleRequiredFields);
        }

        // Initialize on page load
        toggleRequiredFields();

        // Toggle manager approval dropdown based on checkbox
        const requiresManagerApproval = document.getElementById('requires_manager_approval');
        const managerApprovalSelectGroup = document.getElementById('manager_approval_select_group');
        const managerApprovalSelect = document.getElementById('manager_approval_user_id');

        function toggleManagerApproval() {
            if (requiresManagerApproval && managerApprovalSelectGroup) {
                managerApprovalSelectGroup.style.display = requiresManagerApproval.checked ? 'block' : 'none';
                if (managerApprovalSelect) {
                    managerApprovalSelect.required = requiresManagerApproval.checked;
                }
            }
        }

        if (requiresManagerApproval) {
            requiresManagerApproval.addEventListener('change', toggleManagerApproval);
        }

        // Toggle GM approval dropdown based on checkbox
        const requiresGmApproval = document.getElementById('requires_gm_approval');
        const gmApprovalSelectGroup = document.getElementById('gm_approval_select_group');
        const gmApprovalSelect = document.getElementById('gm_approval_user_id');

        function toggleGmApproval() {
            if (requiresGmApproval && gmApprovalSelectGroup) {
                gmApprovalSelectGroup.style.display = requiresGmApproval.checked ? 'block' : 'none';
                if (gmApprovalSelect) {
                    gmApprovalSelect.required = requiresGmApproval.checked;
                }
            }
        }

        if (requiresGmApproval) {
            requiresGmApproval.addEventListener('change', toggleGmApproval);
        }

        // Initialize on page load
        toggleManagerApproval();
        toggleGmApproval();
    });
</script>
</x-app-layout>
