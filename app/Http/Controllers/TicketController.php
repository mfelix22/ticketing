<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Priority;
use App\Models\RequestType;
use App\Models\RequestStatus;
use App\Models\Ticket;
use App\Models\RequestAttachment;
use App\Models\RequestComment;
use App\Notifications\TicketStatusUpdated;
use App\Notifications\NewTicketComment;
use App\Notifications\NewTicketCreated;
use App\Notifications\RequestCancellationRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    /**
     * Requester requests cancellation (set status to Pending Cancellation)
     */
    public function cancel(Request $request, Ticket $ticket)
    {
        $userId = Auth::user()->id;

        // Only the requester can request cancellation
        if ($ticket->requester_id !== $userId) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only the ticket requester can request cancellation.');
        }

        // Prevent if already in finished/cancelled states
        $currentStatus = $ticket->status ? $ticket->status->name : 'Open';
        if (in_array($currentStatus, ['Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User'])) {
            return back()->with('error', 'Ticket is already finished or cancelled.');
        }

        // Validate cancellation reason
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000|min:10'
        ], [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancellation_reason.max' => 'Cancellation reason cannot exceed 1000 characters.'
        ]);

        // Get or create the Pending Cancellation status
        $pendingCancel = \App\Models\RequestStatus::firstOrCreate(
            ['name' => 'Pending Cancellation'],
            ['description' => 'Cancellation requested by user, awaiting PIC approval']
        );

        // Update ticket status to Pending Cancellation
        $ticket->update([
            'status_id' => $pendingCancel->id,
        ]);

        // Add a comment with the cancellation reason
        $userName = Auth::user()->name;
        $commentText = "🔴 CANCELLATION REQUESTED BY USER\n\n";
        $commentText .= "Requester: {$userName}\n";
        $commentText .= "Date: " . now()->format('M d, Y H:i') . "\n\n";
        $commentText .= "Reason for Cancellation:\n";
        $commentText .= $validated['cancellation_reason'];

        \App\Models\RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $commentText
        ]);

        // Notify assigned PIC or department approvers about the cancellation request
        $recipients = collect();

        if ($ticket->assigned_to && $ticket->assignedTo) {
            $recipients->push($ticket->assignedTo);
        } else {
            $departmentUsers = \App\Models\User::where('department_id', $ticket->department_id)
                ->where(function ($q) {
                    $q->whereIn('status', ['Approver', 'Both'])
                        ->orWhere('is_pic', 1);
                })
                ->get();

            $recipients = $recipients->merge($departmentUsers);
        }

        $recipients = $recipients->filter(function ($recipient) use ($userId) {
            return $recipient && $recipient->id !== $userId;
        });

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new RequestCancellationRequested($ticket, Auth::user()));
        }

        return back()->with('success', 'Cancellation request submitted and awaiting PIC approval.');
    }

    /**
     * PIC approves user-requested cancellation (sets status to Cancelled by User)
     */
    public function approveCancel(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $canPicAct = ($user->is_pic ?? false) || in_array($user->status, ['Approver', 'Both', 'Super Admin']);
        if (!$canPicAct) {
            return back()->with('error', 'You do not have permission to approve cancellation.');
        }

        $currentStatus = $ticket->status ? $ticket->status->name : 'Open';
        if ($currentStatus !== 'Pending Cancellation') {
            return back()->with('error', 'Cancellation can only be approved when status is Pending Cancellation.');
        }

        $cancelledByUser = \App\Models\RequestStatus::firstOrCreate(
            ['name' => 'Cancelled by User'],
            ['description' => 'Ticket cancelled after user requested and PIC approved']
        );

        $ticket->update(['status_id' => $cancelledByUser->id]);

        \App\Models\RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => '✅ PIC approved cancellation requested by user.'
        ]);

        return back()->with('success', 'Cancellation approved. Ticket is cancelled by user request.');
    }

    /**
     * PIC directly cancels the ticket (sets status to Cancelled by PIC)
     */
    public function cancelByPic(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $canPicAct = ($user->is_pic ?? false) || in_array($user->status, ['Approver', 'Both', 'Super Admin']);
        if (!$canPicAct) {
            return back()->with('error', 'You do not have permission to cancel as PIC.');
        }

        $currentStatus = $ticket->status ? $ticket->status->name : 'Open';
        if (in_array($currentStatus, ['Completed', 'Closed', 'Cancelled', 'Cancelled by PIC', 'Cancelled by User'])) {
            return back()->with('error', 'Ticket is already finished or cancelled.');
        }

        $cancelledByPic = \App\Models\RequestStatus::firstOrCreate(
            ['name' => 'Cancelled by PIC'],
            ['description' => 'Ticket cancelled directly by PIC/Approver']
        );

        $ticket->update(['status_id' => $cancelledByPic->id]);

        \App\Models\RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => '🚫 Ticket cancelled directly by PIC.'
        ]);

        return back()->with('success', 'Ticket cancelled by PIC.');
    }

    /**
     * Duplicate a ticket with its text fields - redirects to create form with pre-filled data
     */
    public function duplicate(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        // Basic permission: requester can duplicate their ticket; users with Requester/Both status can duplicate
        $canDuplicate = $user->id === $ticket->requester_id || in_array($user->status, ['Requester', 'Both']);
        if (!$canDuplicate) {
            return back()->with('error', 'You do not have permission to duplicate this ticket.');
        }

        // Redirect to create page with pre-filled data
        return redirect()->route('tickets.create')
            ->with('success', 'Ticket data loaded. You can now edit and submit the new ticket.')
            ->with('duplicate_data', [
                'title' => $ticket->title,
                'department_id' => $ticket->department_id,
                'request_type_id' => $ticket->request_type_id,
                'priority_id' => $ticket->priority_id,
                'detail_request' => $ticket->detail_request,
                'assigned_to' => $ticket->assigned_to,
                'schedule_date' => $ticket->schedule_date ? $ticket->schedule_date->format('Y-m-d\TH:i') : null,
            ]);
    }
    public function dashboard()
    {
        $user = Auth::user();

        $openTickets = Ticket::where('requester_id', $user->id)
            ->whereHas('status', function ($q) {
                $q->where('name', 'Open');
            })->count();

        $completedTickets = Ticket::where('requester_id', $user->id)
            ->whereHas('status', function ($q) {
                $q->where('name', 'Completed');
            })->count();

        $pendingApproval = 0;
        $assignedTickets = 0;

        if ($user->status === 'Approver' || $user->status === 'Both') {
            $pendingApproval = Ticket::where('assigned_to', $user->id)
                ->whereHas('status', function ($q) {
                    $q->where('name', 'Pending Approval');
                })->count();

            $assignedTickets = Ticket::where('assigned_to', $user->id)
                ->orWhere('acceptance_user_id', $user->id)
                ->count();
        }

        $recentTickets = Ticket::where('requester_id', $user->id)
            ->with(['status', 'priority'])
            ->latest()
            ->take(5)
            ->get();

        $activities = Ticket::where(function ($query) use ($user) {
            $query->where('requester_id', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhere('acceptance_user_id', $user->id);
        })
            ->with(['status', 'requester'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($ticket) {
                return (object)[
                    'created_at' => $ticket->created_at,
                    'description' => "Ticket #{$ticket->id} {$ticket->status->name}: {$ticket->title}"
                ];
            });

        return view('dashboard', compact(
            'openTickets',
            'completedTickets',
            'pendingApproval',
            'assignedTickets',
            'recentTickets',
            'activities'
        ));
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Redirect Approver-only users to Assigned page
        if ($user->status === 'Approver') {
            return redirect()->route('tickets.assigned')
                ->with('info', 'Approvers can only view tickets assigned to them.');
        }

        // Build query based on user role
        if (in_array($user->status, ['Approver', 'Both'])) {
            // Approvers/PICs see tickets they created OR are explicitly assigned/handling
            $query = Ticket::where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)               // Created by them
                    ->orWhere('assigned_to', $user->id)            // Assigned to them
                    ->orWhere('acceptance_user_id', $user->id)     // Accepted/handling by them
                    ->orWhere('receipt_user_id', $user->id)        // Working as PIC/receipt user
                    ->orWhere('handling_user_id', $user->id)       // Handling user
                    ->orWhere(function ($q2) use ($user) {
                        // Tickets waiting for this manager's approval
                        $q2->where('manager_approval_user_id', $user->id)
                            ->where('manager_approved', false);
                    })
                    ->orWhere(function ($q2) use ($user) {
                        // Tickets waiting for this GM's approval
                        $q2->where('gm_approval_user_id', $user->id)
                            ->where('gm_approved', false);
                    });
            });
        } else {
            // Regular users see only tickets they created OR tickets waiting for their manager/GM approval
            $query = Ticket::where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('manager_approval_user_id', $user->id)
                            ->where('manager_approved', false);
                    })
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('gm_approval_user_id', $user->id)
                            ->where('gm_approved', false);
                    });
            });
        }

        $query->with(['status', 'priority', 'department']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_SEARCH(detail_request, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        $tickets = $query->latest()->paginate(10);
        $statuses = RequestStatus::all();

        // Handle AJAX requests
        if ($request->ajax() || $request->input('ajax') === '1') {
            $html = view('tickets.partials.tickets-table', compact('tickets'))->render();
            return response()->json(['html' => $html]);
        }

        return view('tickets.index', compact('tickets', 'statuses'));
    }

    public function create()
    {
        // Prevent Approver-only users from creating tickets
        if (Auth::user()->status === 'Approver') {
            return redirect()->route('tickets.assigned')
                ->with('error', 'Approvers cannot create tickets. You can only work on assigned tickets.');
        }

        $departments = Department::all();
        $priorities = Priority::all();
        $requestTypes = RequestType::all();

        // Get all PICs for initial load (can be filtered by department via JavaScript)
        $pics = \App\Models\User::where('is_pic', true)->get(['id', 'name', 'department_id']);

        return view('tickets.create', compact('departments', 'priorities', 'requestTypes', 'pics'));
    }

    public function store(Request $request)
    {
        // Prevent Approver-only users from creating tickets
        if (Auth::user()->status === 'Approver') {
            return redirect()->route('tickets.assigned')
                ->with('error', 'Approvers cannot create tickets. You can only work on assigned tickets.');
        }

        // Get user with detailed logging
        Log::info('=== TICKET CREATION DEBUG START ===');
        Log::info('Session ID: ' . session()->getId());
        Log::info('Auth guard: ' . config('auth.defaults.guard'));
        Log::info('Auth check result: ' . (Auth::check() ? 'true' : 'false'));

        $user = Auth::user();
        Log::info('Auth::user() result: ' . ($user ? 'User object found' : 'NULL'));

        if ($user) {
            Log::info('User details: ', [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'id_type' => gettype($user->id)
            ]);
        }

        $userId = $user ? (int)$user->id : null;
        Log::info('Final userId: ' . ($userId ?? 'NULL') . ' (type: ' . gettype($userId) . ')');

        if (!$userId || $userId == 0) {
            Log::error('Authentication failed - redirecting to login');
            return redirect()->route('login')->with('error', 'Please login first to create a ticket.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'request_type_id' => 'required|exists:request_types,id',
            'priority_id' => 'required|exists:priorities,id',
            'detail_request' => 'nullable|array', // Changed to array for dynamic fields
            'assigned_to' => 'nullable|exists:users,id',
            'schedule_date' => 'nullable|date', // Schedule date for delivery planning
            'due_date' => 'nullable|date|after:now', // Optional manual due date
            'attachments.*' => 'nullable|file|max:10240', // 10MB max file size
        ]);

        Log::info('Validation passed', ['validated' => $validated]);

        // Duplicate guard: block accidental double submissions within the last 5 minutes.
        // If the same requester already created a ticket with the same title, department,
        // request type, priority, and detail content, redirect to the existing ticket.
        $recentDuplicate = Ticket::where('requester_id', $userId)
            ->where('title', $validated['title'])
            ->where('department_id', $validated['department_id'])
            ->where('request_type_id', $validated['request_type_id'])
            ->where('priority_id', $validated['priority_id'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->where(function ($query) use ($validated) {
                $detail = $validated['detail_request'] ?? [];
                $query->where('detail_request', json_encode($detail))
                    ->orWhere('detail_request', json_encode((object) $detail));
            })
            ->latest()
            ->first();

        if ($recentDuplicate) {
            Log::warning('Duplicate ticket submission detected', [
                'existing_ticket_id' => $recentDuplicate->id,
                'requester_id' => $userId,
            ]);

            return redirect()->route('tickets.show', $recentDuplicate)
                ->with('warning', 'A ticket with the same title and content was just created. You may have already submitted this request.');
        }

        $maxRetries = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            DB::beginTransaction();

            try {
                // Ensure status exists to prevent null ID issues
                $status = RequestStatus::firstOrCreate(['name' => 'Open'], [
                    'description' => 'Newly created ticket'
                ]);

                // Get priority to calculate due_date based on SLA
                $priority = Priority::find($validated['priority_id']);

                // Determine schedule_date (defaults to now if not provided or empty)
                $scheduleDate = (!empty($validated['schedule_date']) && $validated['schedule_date'] !== '')
                    ? \Carbon\Carbon::parse($validated['schedule_date'])
                    : \Carbon\Carbon::now();

                // ALWAYS calculate due_date from schedule_date + SLA hours
                $slaHours = $priority ? $priority->sla_resolution_hours : 24;
                $dueDate = \Carbon\Carbon::parse($scheduleDate)->addHours($slaHours);

                Log::info('Date Calculation', [
                    'schedule_date_input' => $validated['schedule_date'] ?? 'null',
                    'schedule_date_final' => $scheduleDate,
                    'priority_id' => $validated['priority_id'],
                    'sla_hours' => $slaHours ?? 'not set',
                    'due_date_calculated' => $dueDate,
                ]);

                $ticket = Ticket::create([
                    'requester_id' => (int) $userId,
                    'department_id' => (int) $validated['department_id'],
                    'request_type_id' => (int) $validated['request_type_id'],
                    'priority_id' => (int) $validated['priority_id'],
                    'status_id' => (int) $status->id,
                    'title' => $validated['title'],
                    'detail_request' => $validated['detail_request'] ?? [], // Store as JSON array
                    'assigned_to' => $validated['assigned_to'] ? (int) $validated['assigned_to'] : null,
                    'schedule_date' => $scheduleDate,
                    'due_date' => $dueDate,
                ]);

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('attachments', 'public');

                        RequestAttachment::create([
                            'request_id' => $ticket->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'uploaded_by' => (int) $userId,
                        ]);
                    }
                }

                DB::commit();

                Log::info('Ticket created successfully', ['ticket_id' => $ticket->id]);

                // Send notifications to relevant users
                $this->notifyNewTicket($ticket);

                return redirect()->route('tickets.show', $ticket)
                    ->with('success', 'Ticket created successfully.');
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                // Retry on duplicate request_number race condition
                if (
                    $attempt < $maxRetries
                    && str_contains($e->getMessage(), 'Duplicate entry')
                    && str_contains($e->getMessage(), 'request_number')
                ) {
                    Log::warning('Duplicate request_number detected, retrying attempt ' . $attempt);
                    usleep(100000 * $attempt); // 100ms, 200ms, 300ms
                    continue;
                }

                $lastException = $e;
                break;
            } catch (\Throwable $e) {
                DB::rollBack();
                $lastException = $e;
                break;
            }
        }

        if ($lastException) {
            Log::error('Ticket creation failed', [
                'error' => $lastException->getMessage(),
                'trace' => $lastException->getTraceAsString(),
                'line' => $lastException->getLine(),
                'file' => $lastException->getFile()
            ]);
            return back()->withInput()->with('error', 'Failed to create ticket: ' . $lastException->getMessage());
        }
    }

    public function show(Ticket $ticket)
    {
        // Check if user has permission to view this ticket
        $user = Auth::user();

        // User can view if they are:
        // - The requester
        // - Assigned to the ticket
        // - Handling the ticket (receipt_user_id or handling_user_id)
        // - A PIC in the same department as the ticket
        // - An Approver/Both status
        // - In the same department as the ticket (for delivery packages visibility)
        // - The manager selected for approval
        $canView = $user->id === $ticket->requester_id ||
            $user->id === $ticket->assigned_to ||
            $user->id === $ticket->acceptance_user_id ||
            $user->id === $ticket->receipt_user_id ||
            $user->id === $ticket->handling_user_id ||
            $user->id === $ticket->manager_approval_user_id ||
            $user->id === $ticket->gm_approval_user_id ||
            ($user->is_pic && $user->department_id && $user->department_id === $ticket->department_id) ||
            in_array($user->status, ['Approver', 'Both']) ||
            ($user->department_id && $user->department_id === $ticket->department_id);

        if (!$canView) {
            return redirect()->route('tickets.index')
                ->with('error', 'You do not have permission to view this ticket.');
        }

        $ticket->load([
            'requester',
            'department',
            'requestType.fields',
            'priority',
            'status',
            'assignedTo',
            'acceptanceUser',
            'receiptUser',
            'handlingUser',
            'managerApprovalUser',
            'managerApprovedByUser',
            'gmApprovalUser',
            'gmApprovedByUser',
            'comments.user',
            'attachments',
            'history.user'
        ]);

        // Mark notification as read if coming from notification
        if (request()->has('notification')) {
            $notification = Auth::user()->notifications()->find(request('notification'));
            if ($notification) {
                $notification->markAsRead();
            }
        }

        // Get all statuses for the dropdown (if user has permission to change status)
        $statuses = RequestStatus::all();

        // Determine if IT or Purchasing department for conditional form fields
        $isITDept = $ticket->department->name === 'IT';
        $isPurchasingDept = $ticket->department->name === 'Purchasing';
        $isITOrPurchasing = $isITDept || $isPurchasingDept;

        // Load managers for IT department manager approval dropdown
        $managers = \App\Models\User::where('role', 'manager')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Load general managers for IT department GM approval dropdown
        $generalManagers = \App\Models\User::where('role', 'general_manager')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tickets.show', compact('ticket', 'statuses', 'isITOrPurchasing', 'managers', 'generalManagers'));
    }

    public function edit(Ticket $ticket)
    {
        $user = Auth::user();

        // Check if user can edit this ticket
        $canEdit = $user->id === $ticket->requester_id ||
            in_array($user->status, ['Requester', 'Both']);

        if (!$canEdit) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'You do not have permission to edit this ticket.');
        }

        // Only allow editing Open tickets
        if ($ticket->status->name !== 'Open') {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only Open tickets can be edited.');
        }

        $departments = Department::all();
        $priorities = Priority::all();
        $requestTypes = RequestType::all();
        $pics = \App\Models\User::where('is_pic', true)->get(['id', 'name', 'department_id']);

        return view('tickets.edit', compact('ticket', 'departments', 'priorities', 'requestTypes', 'pics'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        // Check if user can edit this ticket
        $canEdit = $user->id === $ticket->requester_id ||
            in_array($user->status, ['Requester', 'Both']);

        if (!$canEdit) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'You do not have permission to edit this ticket.');
        }

        // Only allow editing Open tickets
        if ($ticket->status->name !== 'Open') {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only Open tickets can be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'request_type_id' => 'required|exists:request_types,id',
            'priority_id' => 'required|exists:priorities,id',
            'detail_request' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
            'schedule_date' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();

        try {
            // Get priority to recalculate due_date if schedule_date or priority changes
            $priority = Priority::find($validated['priority_id']);
            $scheduleDate = (!empty($validated['schedule_date']) && $validated['schedule_date'] !== '')
                ? \Carbon\Carbon::parse($validated['schedule_date'])
                : $ticket->schedule_date ?? \Carbon\Carbon::now();

            $slaHours = $priority ? $priority->sla_resolution_hours : 24;
            $dueDate = \Carbon\Carbon::parse($scheduleDate)->addHours($slaHours);

            $ticket->update([
                'title' => $validated['title'],
                'department_id' => (int) $validated['department_id'],
                'request_type_id' => (int) $validated['request_type_id'],
                'priority_id' => (int) $validated['priority_id'],
                'detail_request' => $validated['detail_request'] ?? [],
                'assigned_to' => $validated['assigned_to'] ? (int) $validated['assigned_to'] : null,
                'schedule_date' => $scheduleDate,
                'due_date' => $dueDate,
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments', 'public');

                    RequestAttachment::create([
                        'request_id' => $ticket->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'uploaded_by' => (int) $user->id,
                    ]);
                }
            }

            // Add comment about the edit
            RequestComment::create([
                'request_id' => $ticket->id,
                'user_id' => $user->id,
                'comment' => '✏️ Ticket details updated.'
            ]);

            DB::commit();

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Ticket updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Ticket update failed', [
                'error' => $e->getMessage(),
                'ticket_id' => $ticket->id
            ]);
            return back()->withInput()->with('error', 'Failed to update ticket: ' . $e->getMessage());
        }
    }

    public function assigned(Request $request)
    {
        $user = Auth::user();

        // Users can see assigned tickets if they:
        // - Have Approver/Both status, OR
        // - Have is_pic=true (are a Person in Charge), OR
        // - Super Admin (sees all tickets)
        if (!$user->is_pic && !in_array($user->status, ['Approver', 'Both', 'Super Admin'])) {
            return redirect()->route('tickets.index')
                ->with('error', 'You do not have permission to view assigned tickets.');
        }

        // Build query based on user permissions
        $query = Ticket::query();

        if ($user->status === 'Super Admin') {
            // Super Admin sees ALL tickets to monitor and help with any issues
            // No base filtering - they see everything
        } else {
            // Shared: "my involvement" clause for all non-Super Admin users
            $myInvolvement = function ($q) use ($user) {
                $q->where('handling_user_id', $user->id)
                    ->orWhere('receipt_user_id', $user->id)
                    ->orWhere('acceptance_user_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('manager_approval_user_id', $user->id)
                            ->where('manager_approved', false);
                    })
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('gm_approval_user_id', $user->id)
                            ->where('gm_approved', false);
                    });
                if ($user->is_pic) {
                    $q->orWhere('assigned_to', $user->id);
                }
            };

            // Shared: "unassigned in my department" clause
            $unassignedInDept = function ($q) use ($user) {
                $q->where('department_id', $user->department_id)
                    ->whereNull('assigned_to')
                    ->whereNull('handling_user_id')
                    ->whereNull('receipt_user_id')
                    ->whereNull('acceptance_user_id')
                    ->whereHas('status', function ($q3) {
                        $q3->whereIn('name', ['Open', 'Pending Approval', 'Pending Cancellation']);
                    });
            };

            // Wrap base permission conditions in a single WHERE so that
            // subsequent AND filters (status, overdue, handler) apply to the
            // ENTIRE result set, not just the unassigned branch.
            $query->where(function ($q) use ($myInvolvement, $unassignedInDept, $user) {
                $q->where($myInvolvement);
                if ($user->department_id) {
                    $q->orWhere($unassignedInDept);
                }
            });
        }

        // Default to "Open" status on initial page load only.
        // When the user explicitly selects "All Statuses" (status param is present but empty),
        // show ALL statuses including completed/closed/cancelled.
        if (!$request->has('status')) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('status', function ($q2) {
                    $q2->where('name', 'Open');
                })->orWhere(function ($q3) use ($user) {
                    // Also show tickets waiting for this manager's approval
                    $q3->where('manager_approval_user_id', $user->id)
                        ->where('manager_approved', false);
                })->orWhere(function ($q3) use ($user) {
                    // Also show tickets waiting for this GM's approval
                    $q3->where('gm_approval_user_id', $user->id)
                        ->where('gm_approved', false);
                });
            });
        }

        $query->with(['status', 'priority', 'department', 'requester', 'handlingUser', 'assignedTo', 'receiptUser', 'requestType']);

        if ($request->status) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Overdue filter
        if ($request->overdue_filter === 'overdue') {
            $query->where('due_date', '<', now())
                ->whereHas('status', function ($q) {
                    $q->whereNotIn('name', ['Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User', 'Pending Cancellation']);
                });
        } elseif ($request->overdue_filter === 'due_soon') {
            $query->where('due_date', '>', now())
                ->where('due_date', '<=', now()->addHours(24))
                ->whereHas('status', function ($q) {
                    $q->whereNotIn('name', ['Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User', 'Pending Cancellation']);
                });
        }

        // Handler filter - further restricts the base result set
        if ($request->handler_filter === 'my_assignments') {
            $query->where(function ($q) use ($user) {
                $q->where('handling_user_id', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhere('receipt_user_id', $user->id)
                    ->orWhere('acceptance_user_id', $user->id);
            });
        } elseif ($request->handler_filter === 'unassigned') {
            $query->whereNull('handling_user_id')
                ->whereNull('handling_user')
                ->whereNull('assigned_to');
        }

        // Sort: overdue → due soon → by priority (Urgent first) → oldest first within same tier
        // Using subqueries in ORDER BY to avoid interfering with existing WHERE/orWhere conditions
        $tickets = $query
            ->orderByRaw("
                CASE
                    WHEN due_date < NOW()
                         AND status_id NOT IN (
                             SELECT id FROM request_statuses
                             WHERE name IN ('Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User', 'Pending Cancellation')
                         )
                    THEN 0
                    WHEN due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                         AND status_id NOT IN (
                             SELECT id FROM request_statuses
                             WHERE name IN ('Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User', 'Pending Cancellation')
                         )
                    THEN 1
                    ELSE 2
                END ASC
            ")
            ->orderByRaw("
                CASE (SELECT UPPER(name) FROM priorities WHERE id = priority_id)
                    WHEN 'URGENT' THEN 1
                    WHEN 'HIGH'   THEN 2
                    WHEN 'MEDIUM' THEN 3
                    WHEN 'LOW'    THEN 4
                    ELSE 5
                END ASC
            ")
            ->oldest()
            ->paginate(10);

        $statuses = RequestStatus::all();

        return view('tickets.assigned', compact('tickets', 'statuses'));
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string'
        ]);

        $userId = Auth::user()->id;

        // Check if comment contains sensitive financial information
        $commentText = $validated['comment'];
        $sensitiveKeywords = ['completion note', 'expenses', 'expenses value', 'harga lain-lain', 'payment method'];

        $lowerComment = strtolower($commentText);
        foreach ($sensitiveKeywords as $keyword) {
            if (stripos($lowerComment, $keyword) !== false) {
                // If comment contains sensitive information, don't save it
                return back()->with('warning', 'Comment contains sensitive financial information and was not saved.');
            }
        }

        $comment = RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $commentText
        ]);

        // Notify ticket requester and assigned users about new comment
        $usersToNotify = collect([$ticket->requester]);

        if ($ticket->assigned_to) {
            $usersToNotify->push($ticket->assignedTo);
        }

        if ($ticket->acceptance_user_id && $ticket->acceptance_user_id !== $ticket->assigned_to) {
            $usersToNotify->push($ticket->acceptanceUser);
        }

        Notification::send(
            $usersToNotify->unique()->except($userId),
            new NewTicketComment($ticket, $comment)
        );

        return back()->with('success', 'Comment added successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status_id' => 'required|exists:request_statuses,id',
            'acceptance_user_id' => 'nullable|exists:users,id'
        ]);

        $userId = Auth::user()->id;
        $oldStatus = $ticket->status->name;
        $newStatus = RequestStatus::find($validated['status_id']);

        $updateData = [
            'status_id' => $validated['status_id'],
        ];

        // If acceptance_user_id is provided, set it and acceptance_date
        if (isset($validated['acceptance_user_id'])) {
            $updateData['acceptance_user_id'] = $validated['acceptance_user_id'];
            $updateData['acceptance_date'] = now();
        }

        $ticket->update($updateData);

        // Add a comment about the status change
        $commentText = "Status updated from {$oldStatus} to {$newStatus->name}";
        if (isset($validated['acceptance_user_id']) && !$ticket->wasRecentlyCreated) {
            $commentText .= " - Ticket accepted by " . Auth::user()->name;
        }

        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $commentText
        ]);

        // Notify ticket requester about status change
        $ticket->requester->notify(new TicketStatusUpdated($ticket, $oldStatus, $newStatus->name));

        return back()->with('success', 'Ticket status updated successfully.');
    }

    public function receipt(Request $request, Ticket $ticket)
    {
        $currentUser = Auth::user();

        // Only approvers and Super Admin can submit receipts
        if (!in_array($currentUser->status, ['Approver', 'Both', 'Super Admin'])) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only approvers can submit receipt information.');
        }

        // Check if ticket is assigned to specific person (Super Admin can bypass this check)
        if ($ticket->assigned_to && $currentUser->status !== 'Super Admin') {
            // If assigned, only that specific person can approve (unless Super Admin)
            if ($ticket->assigned_to !== $currentUser->id) {
                return redirect()->route('tickets.show', $ticket)
                    ->with('error', 'This ticket is assigned to another user. Only ' . ($ticket->assignedTo?->name ?? 'the assigned user') . ' can approve or reject this ticket.');
            }
        }
        // If not assigned to anyone, any approver can process it
        // Super Admin can process any ticket regardless of assignment

        // Check if receipt already submitted
        if ($ticket->receipt_date) {
            return back()->with('error', 'Receipt has already been submitted for this ticket.');
        }

        // Load request type to check if external handling required
        $ticket->load('requestType', 'department');

        // For IT and Purchasing departments, schedule_date is not required
        $isITDept = $ticket->department->name === 'IT';
        $isPurchasingDept = $ticket->department->name === 'Purchasing';
        $isITOrPurchasing = $isITDept || $isPurchasingDept;

        // Manager approval guard for IT tickets
        if ($isITDept && $ticket->requires_manager_approval && !$ticket->manager_approved) {
            return back()->with('error', 'This ticket requires manager approval before it can be accepted.');
        }

        // GM approval guard for IT tickets
        if ($isITDept && $ticket->requires_gm_approval && !$ticket->gm_approved) {
            return back()->with('error', 'This ticket requires GM approval before it can be accepted.');
        }

        $scheduleDateRule = $isITOrPurchasing ? 'nullable|date' : 'required_if:is_continued,1|nullable|date';

        // Validate based on request type
        if ($ticket->requestType->requires_external_handling) {
            // External handling (courier, etc.)
            $validated = $request->validate([
                'receipt_comment' => 'required|string|max:1000',
                'is_continued' => 'required|boolean',
                'handling_user' => 'required_if:is_continued,1|nullable|string|max:255',
                'schedule_date' => $scheduleDateRule
            ]);
        } else {
            // Internal handling — for IT/Purchasing dept, handling_user_id is auto-set to submitting PIC
            $handlingUserIdRule = ($isITOrPurchasing) ? 'nullable|exists:users,id' : 'required_if:is_continued,1|nullable|exists:users,id';
            $validated = $request->validate([
                'receipt_comment' => 'required|string|max:1000',
                'is_continued' => 'required|boolean',
                'handling_user_id' => $handlingUserIdRule,
                'schedule_date' => $scheduleDateRule
            ]);
        }

        // DEBUG: Log receipt submission data to trace rejection issues
        Log::debug('Ticket receipt submission', [
            'ticket_id' => $ticket->id,
            'request_number' => $ticket->request_number,
            'user_id' => Auth::id(),
            'is_continued_raw' => $request->input('is_continued'),
            'is_continued_validated' => $validated['is_continued'],
            'is_continued_type' => gettype($validated['is_continued']),
            'receipt_comment' => $validated['receipt_comment'],
            'department' => $ticket->department->name ?? 'unknown',
            'is_it_or_purchasing' => $isITOrPurchasing,
            'request_type' => $ticket->requestType->name ?? 'unknown',
            'requires_external_handling' => $ticket->requestType->requires_external_handling ?? null,
        ]);

        $approverName = Auth::user()->name;
        $approverId = Auth::user()->id; // Get the actual numeric ID

        // Update ticket with receipt information
        $updateData = [
            'receipt_date' => now(),
            'receipt_comment' => $validated['receipt_comment'],
            'receipt_user_id' => $approverId,
            'is_continued' => $validated['is_continued'],
        ];

        // Only set schedule_date and handling user if continuing (approving)
        if ($validated['is_continued']) {
            if (!$isITOrPurchasing) {
                // Track schedule_date change (not applicable for IT/Purchasing)
                $oldScheduleDate = $ticket->schedule_date;
                $newScheduleDate = $validated['schedule_date'] ?? null;
                if ($oldScheduleDate != $newScheduleDate && $newScheduleDate) {
                    \App\Models\TicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $approverId,
                        'field_changed' => 'schedule_date',
                        'old_value' => $oldScheduleDate ? $oldScheduleDate->format('Y-m-d H:i:s') : null,
                        'new_value' => \Carbon\Carbon::parse($newScheduleDate)->format('Y-m-d H:i:s'),
                        'description' => 'Schedule date set during ticket approval',
                    ]);
                }
                $updateData['schedule_date'] = $newScheduleDate;
            }

            // Do NOT recalculate due_date - it must stay fixed to track if ticket is late!
            // due_date is set once at creation and never changes
            // schedule_date can change for planning purposes

            // Set handling user based on request type
            if ($ticket->requestType->requires_external_handling) {
                // External - use text name
                $oldHandlingUser = $ticket->handling_user;
                $newHandlingUser = $validated['handling_user'];

                if ($oldHandlingUser != $newHandlingUser) {
                    \App\Models\TicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $approverId,
                        'field_changed' => 'handling_user',
                        'old_value' => $oldHandlingUser,
                        'new_value' => $newHandlingUser,
                        'description' => 'Handler assigned during ticket approval',
                    ]);
                }

                $updateData['handling_user'] = $validated['handling_user'];
                $updateData['handling_user_id'] = null;
                $handlingUserName = $validated['handling_user'];
            } else {
                // Internal — for IT/Purchasing dept, auto-assign to the submitting PIC
                $resolvedHandlingUserId = $isITOrPurchasing ? $approverId : ($validated['handling_user_id'] ?? null);
                $handlingUser = \App\Models\User::find($resolvedHandlingUserId);
                $updateData['handling_user_id'] = $resolvedHandlingUserId;
                $updateData['handling_user'] = $handlingUser ? $handlingUser->name : $approverName;
                $handlingUserName = $handlingUser ? $handlingUser->name : $approverName;
            }
        }

        // Handle approval vs rejection
        if ($validated['is_continued']) {
            // APPROVED - Set to In Progress (acceptance happens later by requester)
            $inProgressStatus = RequestStatus::firstOrCreate(
                ['name' => 'In Progress'],
                ['description' => 'Ticket is being worked on']
            );
            $updateData['status_id'] = $inProgressStatus->id;
            // Do NOT set acceptance_user_id here - that's for requester after completion

            $ticket->update($updateData);

            // Add detailed approval comment
            $commentText = "✅ ASSIGNMENT ACCEPTED by {$approverName}\n\n";
            $commentText .= "📝 Receipt Comment:\n{$validated['receipt_comment']}\n\n";
            $commentText .= "🎯 Status Changed: Open → In Progress\n";
            $commentText .= "👤 PIC: {$approverName}\n";
            $commentText .= "🚚 Handling User: {$handlingUserName}\n";
            if (!$isITOrPurchasing && !empty($validated['schedule_date'])) {
                $commentText .= "📅 Schedule Date: " . \Carbon\Carbon::parse($validated['schedule_date'])->format('M d, Y H:i') . "\n";
            }
            $commentText .= "📅 Approved Date: " . now()->format('M d, Y H:i');

            $message = 'Assignment accepted successfully! Status changed to "In Progress" and you are now working on it.';
        } else {
            // REJECTED - Use Cancelled by PIC status
            $cancelledStatus = RequestStatus::firstOrCreate(
                ['name' => 'Cancelled by PIC'],
                ['description' => 'Ticket cancelled by PIC/Approver during receipt']
            );
            $updateData['status_id'] = $cancelledStatus->id;

            $ticket->update($updateData);

            // DEBUG: Log rejection result to verify persistence
            $ticket->refresh();
            Log::debug('Ticket rejection update completed', [
                'ticket_id' => $ticket->id,
                'request_number' => $ticket->request_number,
                'status_id_set' => $cancelledStatus->id,
                'status_id_actual' => $ticket->status_id,
                'status_name_actual' => $ticket->status->name ?? 'null',
                'is_continued_actual' => $ticket->is_continued,
                'receipt_date_actual' => $ticket->receipt_date,
            ]);

            // Add detailed rejection comment
            $commentText = "❌ TICKET REJECTED by {$approverName}\n\n";
            $commentText .= "📝 Rejection Reason:\n{$validated['receipt_comment']}\n\n";
            $commentText .= "🎯 Status Changed: Open → Cancelled by PIC\n";
            $commentText .= "📅 Date: " . now()->format('M d, Y H:i');

            $message = 'Ticket rejected. Status changed to "Cancelled by PIC".';
        }

        // Create the comment
        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $approverId,
            'comment' => $commentText
        ]);

        // Auto-generate receipt PDF if approved (is_continued = true)
        if ($validated['is_continued']) {
            try {
                $this->generateReceiptPDF($ticket, true);
                $message .= ' Receipt PDF has been generated and saved to attachments.';
            } catch (\Exception $e) {
                Log::error('Failed to generate receipt PDF', [
                    'ticket_id' => $ticket->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the whole operation if PDF generation fails
                $message .= ' (Note: Receipt PDF generation failed - please try printing manually)';
            }
        }

        return back()->with('success', $message);
    }

    public function updateManagerApproval(Request $request, Ticket $ticket)
    {
        $currentUser = Auth::user();

        if ($ticket->department->name !== 'IT') {
            return back()->with('error', 'Manager approval is only available for IT tickets.');
        }

        $canEdit = $currentUser->id === $ticket->requester_id
            || $currentUser->canManageTicket($ticket)
            || $currentUser->department_id === $ticket->department_id;

        if (!$canEdit) {
            return back()->with('error', 'You do not have permission to update manager approval.');
        }

        if ($ticket->manager_approved || $ticket->gm_approved) {
            return back()->with('error', 'Approval settings cannot be changed after an approval has been granted.');
        }

        $validated = $request->validate([
            'requires_manager_approval' => 'required|boolean',
            'manager_approval_user_id' => 'required_if:requires_manager_approval,1|nullable|exists:users,id',
            'requires_gm_approval' => 'nullable|boolean',
            'gm_approval_user_id' => 'required_if:requires_gm_approval,1|nullable|exists:users,id',
        ]);

        $requiresApproval = $request->boolean('requires_manager_approval');
        $requiresGm = $request->boolean('requires_gm_approval');

        // GM approval is sequential to manager approval; cannot require GM without manager
        if ($requiresGm && !$requiresApproval) {
            return back()->with('error', 'Manager approval is required before GM approval.');
        }

        $updateData = [
            'requires_manager_approval' => $requiresApproval,
            'manager_approval_user_id' => $requiresApproval ? $validated['manager_approval_user_id'] : null,
            'manager_approved' => false,
            'manager_approved_by' => null,
            'manager_approved_at' => null,
            'requires_gm_approval' => $requiresGm && $requiresApproval,
            'gm_approval_user_id' => ($requiresGm && $requiresApproval) ? $validated['gm_approval_user_id'] : null,
            'gm_approved' => false,
            'gm_approved_by' => null,
            'gm_approved_at' => null,
        ];

        if ($requiresApproval) {
            $pendingManagerStatus = RequestStatus::firstOrCreate(
                ['name' => 'Pending Manager Approval'],
                ['description' => 'Waiting for manager approval before PIC can accept']
            );
            $updateData['status_id'] = $pendingManagerStatus->id;
        } elseif (in_array($ticket->status?->name, ['Pending Manager Approval', 'Pending GM Approval'])) {
            $openStatus = RequestStatus::firstOrCreate(
                ['name' => 'Open'],
                ['description' => 'Newly created ticket']
            );
            $updateData['status_id'] = $openStatus->id;
        }

        $ticket->update($updateData);

        return back()->with('success', 'Manager approval settings updated successfully.');
    }

    public function approveManager(Ticket $ticket)
    {
        $currentUser = Auth::user();

        if ($ticket->department->name !== 'IT') {
            return back()->with('error', 'Manager approval is only available for IT tickets.');
        }

        if (!$ticket->requires_manager_approval) {
            return back()->with('error', 'Manager approval is not required for this ticket.');
        }

        if ($ticket->manager_approval_user_id !== $currentUser->id && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Only the assigned manager can approve this ticket.');
        }

        if ($ticket->manager_approved) {
            return back()->with('info', 'This ticket has already been approved by a manager.');
        }

        $nextStatus = RequestStatus::firstOrCreate(
            ['name' => 'Open'],
            ['description' => 'Newly created ticket']
        );

        // If GM approval is required and not yet approved, move to Pending GM Approval
        if ($ticket->requires_gm_approval && $ticket->gm_approval_user_id && !$ticket->gm_approved) {
            $nextStatus = RequestStatus::firstOrCreate(
                ['name' => 'Pending GM Approval'],
                ['description' => 'Waiting for GM approval after manager approval']
            );
        }

        $ticket->update([
            'manager_approved' => true,
            'manager_approved_by' => $currentUser->id,
            'manager_approved_at' => now(),
            'status_id' => $nextStatus->id,
        ]);

        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $currentUser->id,
            'comment' => "✅ MANAGER APPROVED by {$currentUser->name}\n\n📅 Approved Date: " . now()->format('M d, Y H:i')
        ]);

        return back()->with('success', 'Ticket approved. PIC can now submit receipt.');
    }

    public function approveGm(Ticket $ticket)
    {
        $currentUser = Auth::user();

        if ($ticket->department->name !== 'IT') {
            return back()->with('error', 'GM approval is only available for IT tickets.');
        }

        if (!$ticket->requires_gm_approval) {
            return back()->with('error', 'GM approval is not required for this ticket.');
        }

        if ($ticket->gm_approval_user_id !== $currentUser->id && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Only the assigned GM can approve this ticket.');
        }

        if (!$ticket->manager_approved) {
            return back()->with('error', 'Manager approval is required before GM approval.');
        }

        if ($ticket->gm_approved) {
            return back()->with('info', 'This ticket has already been approved by a GM.');
        }

        $openStatus = RequestStatus::firstOrCreate(
            ['name' => 'Open'],
            ['description' => 'Newly created ticket']
        );

        $ticket->update([
            'gm_approved' => true,
            'gm_approved_by' => $currentUser->id,
            'gm_approved_at' => now(),
            'status_id' => $openStatus->id,
        ]);

        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $currentUser->id,
            'comment' => "✅ GM APPROVED by {$currentUser->name}\n\n📅 Approved Date: " . now()->format('M d, Y H:i')
        ]);

        return back()->with('success', 'Ticket approved by GM. PIC can now submit receipt.');
    }

    public function complete(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $userId = $user->id;

        // Load request type to check handling requirements
        $ticket->load('requestType', 'receiptUser', 'handlingUser', 'department');

        // Check for Billing Collection department restriction
        $isBillingCollection = $ticket->department && (
            $ticket->department->name === 'Billing Collection' ||
            $ticket->department_id == 26
        );

        if ($isBillingCollection) {
            $allowedUsernames = ['sendy', 'lisa'];
            $isAuthorized = in_array(strtolower($user->username), $allowedUsernames);

            if (!$isAuthorized) {
                return redirect()->route('tickets.show', $ticket)
                    ->with('error', 'Only Sendy or Lisa can mark tickets in Billing Collection as completed.');
            }

            if ($user->department_id && $user->department_id != $ticket->department_id) {
                return redirect()->route('tickets.show', $ticket)
                    ->with('error', 'You can only complete tickets within your own department.');
            }
        } else {
            // Authorization logic based on request type for other departments
            if ($ticket->requestType->requires_external_handling) {
                // External handling (courier) - only PIC can mark as completed
                if ($ticket->receipt_user_id !== $userId) {
                    return redirect()->route('tickets.show', $ticket)
                        ->with('error', 'For external requests, only the PIC (' . ($ticket->receiptUser?->name ?? 'approver') . ') who accepted this assignment can mark it as completed.');
                }
            } else {
                // Internal handling - only the assigned handling user can mark as completed
                if ($ticket->handling_user_id !== $userId) {
                    return redirect()->route('tickets.show', $ticket)
                        ->with('error', 'Only the assigned handling user (' . ($ticket->handlingUser?->name ?? 'assigned user') . ') can mark this as completed.');
                }
            }
        }

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
            'additional_note' => 'nullable|string|max:1000',
            'expenses_value' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'usage_hours' => 'nullable|numeric|min:0',
            'payment_flag' => 'nullable|string|in:Bank,Cash',
            'change_handling_user' => 'nullable|string|max:255',
        ]);

        $isITDepartment = $ticket->department && $ticket->department->name === 'IT';
        $requesterVerificationEnabled = config('app.requester_verification_enabled', true);

        if ($isITDepartment || !$requesterVerificationEnabled) {
            // Skip Pending Approval and go directly to Completed (IT department or disabled verification)
            $targetStatus = RequestStatus::where('name', 'Completed')->first();

            if (!$targetStatus) {
                return back()->with('error', 'Completed status not found in the system.');
            }

            $updateData = [
                'status_id' => $targetStatus->id,
                'finished_date' => now(),
                'note' => $validated['note'],
                'additional_note' => $validated['additional_note'] ?? null,
                'expenses_value' => $validated['expenses_value'] ?? null,
                'other_costs' => $validated['other_costs'] ?? null,
                'usage_hours' => $validated['usage_hours'] ?? null,
                'payment_flag' => $validated['payment_flag'] ?? null,
                'acceptance_user_id' => $userId,
                'acceptance_date' => now(),
            ];

            if (!empty($validated['change_handling_user']) && $validated['change_handling_user'] !== $ticket->handling_user) {
                \App\Models\TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                    'field_changed' => 'handling_user',
                    'old_value' => $ticket->handling_user,
                    'new_value' => $validated['change_handling_user'],
                    'description' => 'Handler name changed during completion (e.g., vehicle breakdown)',
                ]);
                $updateData['handling_user'] = $validated['change_handling_user'];
            }

            $ticket->update($updateData);

            $commentText = "✅ WORK COMPLETED by " . Auth::user()->name . "\n\n";
            if (!empty($validated['additional_note'])) {
                $commentText .= "📋 Additional Note:\n{$validated['additional_note']}\n\n";
            }
            $commentText .= "👤 Handled By: {$ticket->handling_user}\n\n";
            $commentText .= "🎯 Status Changed: In Progress → Completed\n";
            $commentText .= "📅 Completion Date: " . now()->format('M d, Y H:i');

            RequestComment::create([
                'request_id' => $ticket->id,
                'user_id' => $userId,
                'comment' => $commentText
            ]);

            return back()->with('success', 'Work marked as completed! Ticket is now Completed.');
        }

        // Get the Pending Approval status (waiting for requester verification)
        $pendingStatus = RequestStatus::where('name', 'Pending Approval')->first();

        if (!$pendingStatus) {
            return back()->with('error', 'Pending Approval status not found in the system.');
        }

        // Update handling_user if changed, update it and track in history
        $updateData = [
            'status_id' => $pendingStatus->id,
            'finished_date' => now(),
            'note' => $validated['note'],
            'additional_note' => $validated['additional_note'] ?? null,
            'expenses_value' => $validated['expenses_value'] ?? null,
            'other_costs' => $validated['other_costs'] ?? null,
            'usage_hours' => $validated['usage_hours'] ?? null,
            'payment_flag' => $validated['payment_flag'] ?? null,
        ];

        // If courier name was changed, update it and track in history
        if (!empty($validated['change_handling_user']) && $validated['change_handling_user'] !== $ticket->handling_user) {
            \App\Models\TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $userId,
                'field_changed' => 'handling_user',
                'old_value' => $ticket->handling_user,
                'new_value' => $validated['change_handling_user'],
                'description' => 'Handler name changed during completion (e.g., vehicle breakdown)',
            ]);
            $updateData['handling_user'] = $validated['change_handling_user'];
        }

        // Update ticket status and set finished date with all completion data
        $ticket->update($updateData);

        // Add a comment about completion (without sensitive financial information)
        $commentText = "✅ WORK COMPLETED by " . Auth::user()->name . "\n\n";
        if (!empty($validated['additional_note'])) {
            $commentText .= "📋 Additional Note:\n{$validated['additional_note']}\n\n";
        }
        $commentText .= "👤 Handled By: {$ticket->handling_user}\n\n";
        $commentText .= "🎯 Status Changed: In Progress → Pending Approval\n";
        $commentText .= "👤 Waiting for requester verification\n";
        $commentText .= "📅 Completion Date: " . now()->format('M d, Y H:i');

        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $commentText
        ]);

        return back()->with('success', 'Work marked as completed! Ticket status changed to "Pending Approval" - waiting for requester verification.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        $userId = Auth::user()->id;

        // Only the requester can close/verify the ticket
        if ($ticket->requester_id !== $userId) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only the ticket requester can verify and close this ticket.');
        }

        // Ticket must be in Pending Approval status
        if ($ticket->status->name !== 'Pending Approval') {
            return back()->with('error', 'Ticket must be in "Pending Approval" status to be closed.');
        }

        $validated = $request->validate([
            'verification_note' => 'nullable|string|max:1000',
        ]);

        // Get the Completed status
        $completedStatus = RequestStatus::where('name', 'Completed')->first();

        if (!$completedStatus) {
            return back()->with('error', 'Completed status not found in the system.');
        }

        // Update ticket status to Completed and set acceptance fields
        $ticket->update([
            'status_id' => $completedStatus->id,
            'acceptance_user_id' => $userId,
            'acceptance_date' => now(),
        ]);

        // Add a comment about verification
        $commentText = "✅ WORK ACCEPTED by " . Auth::user()->name . " (Requester)\n\n";
        if (!empty($validated['verification_note'])) {
            $commentText .= "📝 Verification Note:\n{$validated['verification_note']}\n\n";
        }
        $commentText .= "🎯 Status Changed: Pending Approval → Completed\n";
        $commentText .= "✔️ Work accepted and verified by requester\n";
        $commentText .= "📅 Acceptance Date: " . now()->format('M d, Y H:i');

        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $commentText
        ]);

        return back()->with('success', 'Ticket verified and closed successfully! Thank you for confirming the work.');
    }

    public function reopen(Request $request, Ticket $ticket)
    {
        $userId = Auth::user()->id;

        // Only the requester can reopen the ticket
        if ($ticket->requester_id !== $userId) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only the ticket requester can reopen this ticket.');
        }

        // Load department to check IT-specific rules
        $ticket->load('department');
        $isITDepartment = $ticket->department && $ticket->department->name === 'IT';

        // Ticket must be in Pending Approval, or Completed if IT department
        $allowedStatuses = ['Pending Approval'];
        if ($isITDepartment) {
            $allowedStatuses[] = 'Completed';
        }

        if (!in_array($ticket->status->name, $allowedStatuses)) {
            return back()->with('error', 'This ticket cannot be reopened from its current status.');
        }

        $validated = $request->validate([
            'reopen_reason' => 'required|string|max:1000',
        ]);

        // Get the In Progress status
        $inProgressStatus = RequestStatus::where('name', 'In Progress')->first();

        if (!$inProgressStatus) {
            return back()->with('error', 'In Progress status not found in the system.');
        }

        $previousStatus = $ticket->status->name;

        // Update ticket status back to In Progress, clear finished_date and acceptance fields
        $ticket->update([
            'status_id' => $inProgressStatus->id,
            'finished_date' => null,
            'acceptance_date' => null,
            'acceptance_user_id' => null,
        ]);

        // Add a comment about reopening
        $commentText = "🔄 TICKET REOPENED by " . Auth::user()->name . " (Requester)\n\n";
        $commentText .= "📝 Reason for Reopening:\n{$validated['reopen_reason']}\n\n";
        $commentText .= "🎯 Status Changed: {$previousStatus} → In Progress\n";
        $commentText .= "⚠️ Additional work required\n";
        $commentText .= "📅 Reopened Date: " . now()->format('M d, Y H:i');

        RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $commentText
        ]);

        return back()->with('success', 'Ticket reopened successfully. The handler has been notified to continue working on it.');
    }

    /**
     * Display all tickets for the user's department.
     * Accessible by managers and operations managers.
     */
    public function departmentTickets(Request $request)
    {
        $user = Auth::user();

        // Only managers and operations managers can view department tickets
        if (!in_array($user->role, ['manager', 'operations_manager'])) {
            return redirect()->route('tickets.index')
                ->with('error', 'Only department managers can view department tickets.');
        }

        if (!$user->department_id) {
            return redirect()->route('tickets.index')
                ->with('error', 'You are not assigned to a department.');
        }

        $query = Ticket::with(['status', 'priority', 'department', 'requester'])
            ->where('department_id', $user->department_id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhereRaw("JSON_EXTRACT(detail_request, '$.recipient_name') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(detail_request, '$.phone_number') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_EXTRACT(detail_request, '$.delivery_address') LIKE ?", ["%{$search}%"])
                    ->orWhereHas('requester', function ($rq) use ($search) {
                        $rq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        $tickets = $query->latest()->paginate(10);
        $statuses = RequestStatus::all();

        // Handle AJAX requests
        if ($request->ajax() || $request->input('ajax') === '1') {
            $html = view('tickets.partials.department-tickets-table', compact('tickets'))->render();
            return response()->json(['html' => $html]);
        }

        return view('tickets.department', compact('tickets', 'statuses'));
    }

    /**
     * Send notifications when a new ticket is created
     */
    protected function notifyNewTicket(Ticket $ticket)
    {
        $usersToNotify = collect();

        // If a specific PIC (assigned_to) is selected, notify only that person
        if ($ticket->assigned_to) {
            $usersToNotify->push($ticket->assignedTo);
            Log::info('Notifying assigned PIC', ['user_id' => $ticket->assigned_to]);
        } else {
            // If no PIC selected, notify all users in the department with Approver or Both status
            $departmentUsers = \App\Models\User::where('department_id', $ticket->department_id)
                ->whereIn('status', ['Approver', 'Both'])
                ->get();

            $usersToNotify = $usersToNotify->merge($departmentUsers);
            Log::info('Notifying department approvers', [
                'department_id' => $ticket->department_id,
                'user_count' => $departmentUsers->count()
            ]);
        }

        // Remove the ticket creator from notifications (don't notify themselves)
        $usersToNotify = $usersToNotify->filter(function ($user) use ($ticket) {
            return $user->id !== $ticket->requester_id;
        });

        // Send notifications
        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new NewTicketCreated($ticket));
            Log::info('New ticket notifications sent', [
                'ticket_id' => $ticket->id,
                'notified_users' => $usersToNotify->pluck('id')->toArray()
            ]);
        } else {
            Log::warning('No users to notify for new ticket', ['ticket_id' => $ticket->id]);
        }
    }

    /**
     * Generate and save receipt PDF for a ticket
     */
    private function generateReceiptPDF(Ticket $ticket, $saveToAttachments = true)
    {
        // Load necessary relations
        $ticket->load(['requester', 'requester.department', 'receiptUser', 'handlingUser', 'requestType', 'priority', 'status']);

        // Generate receipt number
        $receiptNumber = 'RCP-' . date('Y') . '-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT);

        // Render main content HTML
        $html = view('receipts.delivery-receipt', [
            'ticket' => $ticket,
            'receiptNumber' => $receiptNumber
        ])->render();

        // Render header and footer HTML (for repeated display)
        $headerHtml = view('receipts.header', [
            'ticket' => $ticket
        ])->render();
        $footerHtml = view('receipts.footer', [
            'ticket' => $ticket
        ])->render();

        // Initialize mPDF with 22x14cm page (landscape width > height)
        $mpdf = new \Mpdf\Mpdf([
            'format' => [220, 140],
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 18,
            'margin_bottom' => 18,
            'default_font' => 'arial',
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch'
        ]);

        // Set repeated header and footer
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);
        $mpdf->defaultheaderline = 0;
        $mpdf->defaultfooterline = 0;

        // Write HTML content
        $mpdf->WriteHTML($html);

        if ($saveToAttachments) {
            // Save PDF to storage
            $filename = 'receipt_' . $ticket->request_number . '_' . time() . '.pdf';
            $path = 'receipts/' . $filename;

            // Store in public storage
            Storage::disk('public')->put($path, $mpdf->Output('', 'S'));

            // Save as attachment record
            RequestAttachment::create([
                'request_id' => $ticket->id,
                'file_path' => $path,
                'file_name' => $filename,
                'file_size' => Storage::disk('public')->size($path),
                'mime_type' => 'application/pdf',
                'uploaded_by' => Auth::id()
            ]);

            Log::info('Receipt PDF generated and saved', [
                'ticket_id' => $ticket->id,
                'filename' => $filename,
                'path' => $path
            ]);
        }

        return $mpdf;
    }

    /**
     * Print/download receipt PDF
     */
    public function printReceipt(Ticket $ticket)
    {
        $user = Auth::user();

        // Check if receipt has been created
        if (!$ticket->receipt_date) {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'No receipt available for this ticket yet.');
        }

        // Generate PDF (don't save again)
        $mpdf = $this->generateReceiptPDF($ticket, false);

        // Stream PDF to browser
        return response($mpdf->Output('Receipt_' . $ticket->request_number . '.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Receipt_' . $ticket->request_number . '.pdf"'
        ]);
    }

    public function updateDeliveryPic(Request $request, Ticket $ticket)
    {
        // Check if user is from the same department (manager/admin privilege)
        if (Auth::user()->department_id !== $ticket->department_id) {
            return back()->with('error', 'You do not have permission to update the delivery PIC.');
        }

        $validated = $request->validate([
            'delivery_pic_override' => 'required|string|max:255'
        ]);

        // Find the user by name to update the actual assignment
        $picUser = User::where('name', $validated['delivery_pic_override'])
            ->whereIn('status', ['PIC', 'Both'])
            ->first();

        if (!$picUser) {
            return back()->with('error', 'PIC user not found.');
        }

        // Update the actual assignment
        $ticket->update([
            'assigned_to' => $picUser->id,
            'receipt_user_id' => $picUser->id
        ]);

        return back()->with('success', 'Delivery PIC updated successfully to: ' . $picUser->name);
    }
}
