<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourierController extends Controller
{
    /**
     * Display courier packages history grouped by handler
     * Shows ALL courier tickets (including completed) for historical tracking
     * Only accessible by Requester or Approver users from IT, Purchasing, or BC departments
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $allowedStatuses = ['Requester', 'Approver', 'Both'];
        $allowedDepartments = ['IT', 'Purchasing', 'Billing Collection'];

        if (!in_array($user->status, $allowedStatuses) || !$user->department || !in_array($user->department->name, $allowedDepartments)) {
            return redirect()->route('tickets.index')
                ->with('error', 'Access denied. Only Requester or Approver users from IT, Purchasing, or BC departments can view courier packages.');
        }
        
        $query = Ticket::with(['requester', 'priority', 'status', 'handlingUser', 'receiptUser', 'assignedTo', 'department', 'requestType'])
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            });

        // Filter by department (only super_admin sees everything)
        if ($user->role !== 'super_admin') {
            $query->where('department_id', $user->department_id);
        }

        // Default date range: This month (if no dates provided)
        // Use schedule_date if available, otherwise fall back to created_at
        if (!$request->filled('date_from') && !$request->filled('date_to') && !$request->filled('date_range')) {
            $query->where(function ($q) {
                $q->whereDate('schedule_date', '>=', now()->startOfMonth())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '>=', now()->startOfMonth());
                  });
            });
        }

        // Date range presets
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->where(function ($q) {
                        $q->whereDate('schedule_date', today())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', today());
                          });
                    });
                    break;
                case 'this_week':
                    $query->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                          });
                    });
                    break;
                case 'this_month':
                    $query->where(function ($q) {
                        $q->whereDate('schedule_date', '>=', now()->startOfMonth())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', '>=', now()->startOfMonth());
                          });
                    });
                    break;
                case 'last_month':
                    $query->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                          });
                    });
                    break;
                // 'all_time' - no filter
            }
        }

        // Custom date range (overrides presets)
        // Use schedule_date if available, otherwise fall back to created_at
        if ($request->filled('date_from')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '>=', $request->date_from)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '>=', $request->date_from);
                  });
            });
        }
        if ($request->filled('date_to')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '<=', $request->date_to)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '<=', $request->date_to);
                  });
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Filter by specific courier if provided
        if ($request->filled('courier')) {
            $query->where('handling_user', $request->courier);
        }

        // HISTORICAL VIEW: Show all tickets including completed (users can filter if needed)
        // This makes it a proper history/report page

        // Order by created date (newest first) for historical view
        $query->orderBy('created_at', 'desc');

        $tickets = $query->get();

        // Group tickets by courier display name (trimmed). Empty/null names go to "Unassigned"
        $groups = $tickets->groupBy(function ($t) {
            $raw = $t->handling_user;
            // Handle null, empty string, or string "NULL"
            if ($raw === null || $raw === '' || strtoupper(trim($raw)) === 'NULL') {
                return 'Unassigned';
            }
            return trim($raw);
        });

        // Get unique courier names for filter (only from Delivery request type)
        $couriers = Ticket::whereNotNull('handling_user')
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            })
            ->select('handling_user')
            ->distinct()
            ->pluck('handling_user')
            ->sort();

        // Get all statuses for filter
        $statuses = \App\Models\RequestStatus::all();

        return view('courier.index', compact('tickets', 'couriers', 'statuses', 'groups'));
    }

    /**
     * Display courier packages assigned to current user
     * Shows ALL courier tickets (past and present)
     * Only accessible by Requester or Approver users from IT, Purchasing, or BC departments
     */
    public function today(Request $request)
    {
        $user = Auth::user();

        $allowedStatuses = ['Requester', 'Approver', 'Both'];
        $allowedDepartments = ['IT', 'Purchasing', 'Billing Collection'];

        if (!in_array($user->status, $allowedStatuses) || !$user->department || !in_array($user->department->name, $allowedDepartments)) {
            return redirect()->route('tickets.index')
                ->with('error', 'Access denied. Only Requester or Approver users from IT, Purchasing, or BC departments can view delivery packages.');
        }

        // Build query for delivery packages
        $myTicketsQuery = Ticket::with(['requester', 'priority', 'status', 'handlingUser', 'receiptUser', 'assignedTo', 'department', 'requestType'])
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            });

        // super_admin and operations_manager see ALL tickets from ALL departments
        // Regular users and managers see only their department
        if (!in_array($user->role, ['super_admin', 'operations_manager'])) {
            $myTicketsQuery->where('department_id', $user->department_id);
        } else {
            // If operations_manager or super_admin, allow department filtering
            if ($request->filled('department_id')) {
                $myTicketsQuery->where('department_id', $request->department_id);
            }
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $myTicketsQuery->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Date range presets
        if ($request->filled('date_range') && $request->date_range !== 'custom') {
            switch ($request->date_range) {
                case 'today':
                    $myTicketsQuery->where(function ($q) {
                        $q->whereDate('schedule_date', today())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', today());
                          });
                    });
                    break;
                case 'this_week':
                    $myTicketsQuery->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                          });
                    });
                    break;
                case 'this_month':
                    $myTicketsQuery->where(function ($q) {
                        $q->whereDate('schedule_date', '>=', now()->startOfMonth())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', '>=', now()->startOfMonth());
                          });
                    });
                    break;
                case 'last_month':
                    $myTicketsQuery->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                          });
                    });
                    break;
                // 'all_time' - no filter
            }
        }

        // Custom date range (overrides presets) - use schedule_date with created_at fallback
        if ($request->filled('date_from')) {
            $myTicketsQuery->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '>=', $request->date_from)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '>=', $request->date_from);
                  });
            });
        }
        if ($request->filled('date_to')) {
            $myTicketsQuery->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '<=', $request->date_to)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '<=', $request->date_to);
                  });
            });
        }

        // Filter by courier if provided
        if ($request->filled('courier')) {
            $myTicketsQuery->where('handling_user', $request->courier);
        }

        // Filter by assignment status
        \Log::info('Assignment filter check', [
            'has_assignment' => $request->has('assignment'),
            'filled_assignment' => $request->filled('assignment'),
            'assignment_value' => $request->assignment,
            'all_request' => $request->all()
        ]);
        
        if ($request->filled('assignment')) {
            \Log::info('Assignment filter applied', ['assignment' => $request->assignment]);
            if ($request->assignment === 'unassigned') {
                // Unassigned: either no PIC accepted OR no handler/courier set (null, empty, or literal 'NULL')
                $myTicketsQuery->where(function ($q) {
                    $q->whereNull('receipt_user_id')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('handling_user')
                             ->orWhere('handling_user', '=', '')
                             ->orWhereRaw("UPPER(TRIM(handling_user)) = 'NULL'");
                      });
                });
            } elseif ($request->assignment === 'assigned') {
                $myTicketsQuery->whereNotNull('receipt_user_id')
                               ->where(function ($q2) {
                                   $q2->whereNotNull('handling_user')
                                      ->where('handling_user', '<>', '')
                                      ->whereRaw("UPPER(TRIM(handling_user)) <> 'NULL'");
                               });
            }
        }

        // Text search across common fields
        if ($request->filled('q')) {
            $term = trim($request->q);
            $like = "%{$term}%";
            $lowerTerm = strtolower($term);
            $lowerLike = "%{$lowerTerm}%";
            $myTicketsQuery->where(function ($q) use ($like, $lowerLike) {
                $q->where('request_number', 'like', $like)
                  ->orWhere('title', 'like', $like)
                  ->orWhere('handling_user', 'like', $like)
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, "$.recipient_name"))) LIKE ?', [$lowerLike])
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, "$.phone_number"))) LIKE ?', [$lowerLike])
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, "$.delivery_address"))) LIKE ?', [$lowerLike])
                  ->orWhereHas('requester', function ($rq) use ($like) {
                      $rq->where('name', 'like', $like);
                  });
            });
        }

        // Get courier tickets
        // super_admin sees ALL tickets from all departments
        // Regular users and managers see ALL courier tickets within their department (no per-user restriction)
        
        // Debug: Log the SQL query
        \Log::info('MyTickets SQL', [
            'sql' => $myTicketsQuery->toSql(),
            'bindings' => $myTicketsQuery->getBindings()
        ]);
        
        $myTickets = $myTicketsQuery
            ->orderByRaw('CASE WHEN due_date < NOW() THEN 0 ELSE 1 END')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'myPage')
            ->withQueryString();
            
        \Log::info('MyTickets count', ['count' => $myTickets->total()]);

        // Get TODAY's + ALL UNASSIGNED delivery tickets (from department only, unless super_admin)
        // This includes packages that are:
        // 1. Created today, OR
        // 2. Unassigned (no receipt_user_id - meaning no PIC has accepted it yet) from ANY date
        $allTicketsQuery = Ticket::with(['requester', 'priority', 'status', 'handlingUser', 'receiptUser', 'assignedTo', 'department', 'requestType'])
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            });

        // super_admin and operations_manager see all departments, others see only their department
        if (!in_array($user->role, ['super_admin', 'operations_manager'])) {
            $allTicketsQuery->where('department_id', $user->department_id);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $allTicketsQuery->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Date range presets
        if ($request->filled('date_range') && $request->date_range !== 'custom') {
            switch ($request->date_range) {
                case 'today':
                    $allTicketsQuery->where(function ($q) {
                        $q->whereDate('schedule_date', today())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', today());
                          });
                    });
                    break;
                case 'this_week':
                    $allTicketsQuery->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                          });
                    });
                    break;
                case 'this_month':
                    $allTicketsQuery->where(function ($q) {
                        $q->whereDate('schedule_date', '>=', now()->startOfMonth())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', '>=', now()->startOfMonth());
                          });
                    });
                    break;
                case 'last_month':
                    $allTicketsQuery->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                          });
                    });
                    break;
                // 'all_time' - no filter
            }
        }

        // Custom date range - use schedule_date with created_at fallback
        if ($request->filled('date_from')) {
            $allTicketsQuery->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '>=', $request->date_from)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '>=', $request->date_from);
                  });
            });
        }
        if ($request->filled('date_to')) {
            $allTicketsQuery->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '<=', $request->date_to)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '<=', $request->date_to);
                  });
            });
        }

        // Filter by courier if provided
        if ($request->filled('courier')) {
            $allTicketsQuery->where('handling_user', $request->courier);
        }

        // Text search across common fields
        if ($request->filled('q')) {
            $term = trim($request->q);
            $like = "%{$term}%";
            $lowerTerm = strtolower($term);
            $lowerLike = "%{$lowerTerm}%";
            $allTicketsQuery->where(function ($q) use ($like, $lowerLike) {
                $q->where('request_number', 'like', $like)
                  ->orWhere('title', 'like', $like)
                  ->orWhere('handling_user', 'like', $like)
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, "$.recipient_name"))) LIKE ?', [$lowerLike])
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, "$.phone_number"))) LIKE ?', [$lowerLike])
                  ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, "$.delivery_address"))) LIKE ?', [$lowerLike])
                  ->orWhereHas('requester', function ($rq) use ($like) {
                      $rq->where('name', 'like', $like);
                  });
            });
        }

        // For super_admin, show ALL tickets (no restriction to today/unassigned)
        // For others (including manager):
        // - If NO date filters are provided (no date_from, date_to, OR date_range), show today OR unassigned (default behavior)
        // - If ANY date filters ARE applied, respect the date filter and skip the default restriction
        $hasDateFilter = $request->filled('date_from') || $request->filled('date_to') || $request->filled('date_range');

        // Filter by assignment status (applies to both tables for consistency)
        if ($request->filled('assignment')) {
            if ($request->assignment === 'unassigned') {
                $allTicketsQuery->where(function ($q) {
                    $q->whereNull('receipt_user_id')
                      ->orWhere(function ($q2) {
                          $q2->whereNull('handling_user')
                             ->orWhere('handling_user', '=', '')
                             ->orWhereRaw("UPPER(TRIM(handling_user)) = 'NULL'");
                      });
                });
            } elseif ($request->assignment === 'assigned') {
                $allTicketsQuery->whereNotNull('receipt_user_id')
                                ->where(function ($q2) {
                                    $q2->whereNotNull('handling_user')
                                       ->where('handling_user', '<>', '')
                                       ->whereRaw("UPPER(TRIM(handling_user)) <> 'NULL'");
                                });
            }
        }

        // Calculate total packages count for stats (without unassigned filter)
        $totalPackagesQuery = Ticket::whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            });
        
        // Apply same department filter
        if (!in_array($user->role, ['super_admin', 'operations_manager'])) {
            $totalPackagesQuery->where('department_id', $user->department_id);
        } else {
            // If operations_manager or super_admin with department filter
            if ($request->filled('department_id')) {
                $totalPackagesQuery->where('department_id', $request->department_id);
            }
        }
        
        // Apply same status filter
        if ($request->filled('status')) {
            $totalPackagesQuery->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }
        
        // Apply same date filters for accurate stats
        if ($request->filled('date_range') && $request->date_range !== 'custom') {
            switch ($request->date_range) {
                case 'today':
                    $totalPackagesQuery->where(function ($q) {
                        $q->whereDate('schedule_date', today())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', today());
                          });
                    });
                    break;
                case 'this_week':
                    $totalPackagesQuery->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                          });
                    });
                    break;
                case 'this_month':
                    $totalPackagesQuery->where(function ($q) {
                        $q->whereDate('schedule_date', '>=', now()->startOfMonth())
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereDate('created_at', '>=', now()->startOfMonth());
                          });
                    });
                    break;
                case 'last_month':
                    $totalPackagesQuery->where(function ($q) {
                        $q->whereBetween('schedule_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                          ->orWhere(function ($q2) {
                              $q2->whereNull('schedule_date')
                                 ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                          });
                    });
                    break;
            }
        }
        
        if ($request->filled('date_from')) {
            $totalPackagesQuery->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '>=', $request->date_from)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '>=', $request->date_from);
                  });
            });
        }
        if ($request->filled('date_to')) {
            $totalPackagesQuery->where(function ($q) use ($request) {
                $q->whereDate('schedule_date', '<=', $request->date_to)
                  ->orWhere(function ($q2) use ($request) {
                      $q2->whereNull('schedule_date')
                         ->whereDate('created_at', '<=', $request->date_to);
                  });
            });
        }
        
        // Calculate statistics for cards (Option 3: Show both today's and total stats)
        
        // 1. TODAY'S PACKAGES (for daily operations)
        $todayPackagesQuery = clone $totalPackagesQuery;
        $todayPackagesQuery->where(function ($q) {
            $q->whereDate('schedule_date', today())
               ->orWhere(function ($q2) {
                   $q2->whereNull('schedule_date')
                      ->whereDate('created_at', today());
               });
        });
        $todayPackagesCount = $todayPackagesQuery->count();
        
        // 2. TODAY'S UNASSIGNED
        $todayUnassignedCount = (clone $todayPackagesQuery)->where(function ($q) {
            $q->whereNull('receipt_user_id')
              ->orWhere(function ($q2) {
                  $q2->whereNull('handling_user')
                     ->orWhere('handling_user', '=', '')
                     ->orWhereRaw("UPPER(TRIM(handling_user)) = 'NULL'");
              });
        })->count();
        
        // 3. TOTAL UNASSIGNED (all dates - for backlog visibility)
        $totalUnassignedQuery = clone $totalPackagesQuery;
        $totalUnassignedCount = $totalUnassignedQuery->where(function ($q) {
            $q->whereNull('receipt_user_id')
              ->orWhere(function ($q2) {
                  $q2->whereNull('handling_user')
                     ->orWhere('handling_user', '=', '')
                     ->orWhereRaw("UPPER(TRIM(handling_user)) = 'NULL'");
              });
        })->count();
        
        // 4. OVERDUE (due_date has passed) for open / in-progress tickets (regardless of assignment or filters)
        // Create independent query so it counts all overdue tickets regardless of request filters
        $overdueUnassignedQuery = Ticket::whereHas('requestType', function ($q) {
            $q->where('name', 'Delivery')->orWhere('name', 'Courier');
        });
        
        // Apply department filter only
        if (!in_array($user->role, ['super_admin', 'operations_manager'])) {
            $overdueUnassignedQuery->where('department_id', $user->department_id);
        } else {
            // If operations_manager or super_admin with department filter
            if ($request->filled('department_id')) {
                $overdueUnassignedQuery->where('department_id', $request->department_id);
            }
        }
        
        $overdueUnassignedCount = $overdueUnassignedQuery
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['Open', 'In Progress']);
            })
            ->count();
        
        \Log::info('Stats calculation', [
            'today_total' => $todayPackagesCount,
            'today_unassigned' => $todayUnassignedCount,
            'total_unassigned' => $totalUnassignedCount,
            'overdue_unassigned' => $overdueUnassignedCount
        ]);
        
        // For "All Delivery Requests (Today + Unassigned)" table:
        // By default, show only UNASSIGNED tickets (unless assignment filter overrides it)
        if (!$request->filled('assignment')) {
            // No assignment filter selected: default to showing ONLY unassigned
            $allTicketsQuery->where(function ($q) {
                $q->whereNull('receipt_user_id')
                  ->orWhere(function ($q2) {
                      $q2->whereNull('handling_user')
                         ->orWhere('handling_user', '=', '')
                         ->orWhereRaw("UPPER(TRIM(handling_user)) = 'NULL'");
                  });
            });
        }
        
        // If no date filters are provided and user is not super_admin, show today OR unassigned
        // This ensures we see today's unassigned + older unassigned tickets
        $allTickets = $allTicketsQuery
            ->when($user->role !== 'super_admin' && !$hasDateFilter && !$request->filled('assignment'), function ($q) {
                // Show today's unassigned tickets OR older unassigned tickets
                // (The unassigned filter is already applied above)
            })
            ->orderByRaw('CASE WHEN due_date < NOW() THEN 0 ELSE 1 END')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'allPage')
            ->withQueryString();

        // Get all previous courier/expedition names for autocomplete
        $courierNames = Ticket::whereNotNull('handling_user')
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            })
            ->select('handling_user')
            ->distinct()
            ->orderBy('handling_user')
            ->pluck('handling_user');

        // Get all statuses for filter
        $statuses = \App\Models\RequestStatus::all();

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            // Convert items to array with all attributes
            $myTicketsData = $myTickets->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'request_number' => $ticket->request_number,
                    'title' => $ticket->title,
                    'handling_user' => $ticket->handling_user,
                    'schedule_date' => $ticket->schedule_date,
                    'due_date' => $ticket->due_date,
                    'detail_request' => $ticket->detail_request,
                    'requester' => $ticket->requester ? ['name' => $ticket->requester->name] : null,
                    'status' => $ticket->status ? ['name' => $ticket->status->name] : null,
                ];
            });
            
            $allTicketsData = $allTickets->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'request_number' => $ticket->request_number,
                    'title' => $ticket->title,
                    'handling_user' => $ticket->handling_user,
                    'schedule_date' => $ticket->schedule_date,
                    'due_date' => $ticket->due_date,
                    'detail_request' => $ticket->detail_request,
                    'requester' => $ticket->requester ? ['name' => $ticket->requester->name] : null,
                    'status' => $ticket->status ? ['name' => $ticket->status->name] : null,
                ];
            });
            
            return response()->json([
                'myTickets' => [
                    'data' => $myTicketsData,
                    'current_page' => $myTickets->currentPage(),
                    'last_page' => $myTickets->lastPage(),
                    'total' => $myTickets->total(),
                    'per_page' => $myTickets->perPage(),
                ],
                'allTickets' => [
                    'data' => $allTicketsData,
                    'current_page' => $allTickets->currentPage(),
                    'last_page' => $allTickets->lastPage(),
                    'total' => $allTickets->total(),
                    'per_page' => $allTickets->perPage(),
                ],
                'stats' => [
                    'myTotal' => $myTickets->total(),
                    'todayTotal' => $todayPackagesCount,
                    'todayUnassigned' => $todayUnassignedCount,
                    'totalUnassigned' => $totalUnassignedCount,
                    'overdueUnassigned' => $overdueUnassignedCount,
                ],
            ]);
        }

        // Get all departments for filter (only for operations_manager and super_admin)
        $departments = [];
        if (in_array($user->role, ['super_admin', 'operations_manager'])) {
            $departments = Department::orderBy('name')->get();
        }

        return view('courier.today', compact(
            'allTickets', 
            'myTickets', 
            'courierNames', 
            'statuses',
            'departments',
            'todayPackagesCount',
            'todayUnassignedCount',
            'totalUnassignedCount',
            'overdueUnassignedCount'
        ));
    }

    /**
     * Print manifest for a specific courier
     */
    public function printManifest(Request $request, $courierName)
    {
        $query = Ticket::with(['requester', 'priority', 'status', 'department', 'requestType', 'comments'])
            ->where('handling_user', $courierName)
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            })
            ->whereHas('status', function ($q) {
                $q->whereNotIn('name', ['Completed', 'Closed', 'Cancelled by PIC', 'Cancelled by User', 'Pending Cancellation']);
            });

        // Filter by schedule date if provided
        if ($request->filled('schedule_from')) {
            $query->whereDate('schedule_date', '>=', $request->schedule_from);
        }
        if ($request->filled('schedule_to')) {
            $query->whereDate('schedule_date', '<=', $request->schedule_to);
        }

        $tickets = $query->orderBy('schedule_date', 'asc')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('courier.manifest', compact('tickets', 'courierName'));
    }

    /**
     * Print label for a specific courier request (box label)
     */
    public function printLabel($ticketId)
    {
        $ticket = Ticket::with(['requestType'])->findOrFail($ticketId);

        // Ensure it's a delivery request
        if (!in_array($ticket->requestType->name, ['Delivery', 'Courier'])) {
            return redirect()->route('courier.today')
                ->with('error', 'This is not a delivery request');
        }

        return view('courier.label', compact('ticket'));
    }

    /**
     * Print delivery receipt for a specific courier request
     */
    public function printDeliveryReceipt($ticketId)
    {
        $ticket = Ticket::with([
            'requestType',
            'requester',
            'department',
            'comments',
            'receiptUser',
            'handlingUser'
        ])->findOrFail($ticketId);

        // Ensure it's a delivery request
        if (!in_array($ticket->requestType->name, ['Delivery', 'Courier'])) {
            return redirect()->route('courier.today')
                ->with('error', 'This is not a delivery request');
        }

        $receiptNumber = 'RCP-' . $ticket->request_number;

        return view('receipts.delivery-receipt', compact('ticket', 'receiptNumber'));
    }
}
