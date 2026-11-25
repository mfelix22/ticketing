<?php

namespace App\Livewire\Courier;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TodayTickets extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public string $date_range = '';
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $courier = null;
    public ?string $status = null;
    public string $q = '';

    // Pagination sizes
    public int $perPage = 25;

    protected $queryString = [
        'date_range' => [ 'except' => '' ],
        'date_from'  => [ 'except' => null ],
        'date_to'    => [ 'except' => null ],
        'courier'    => [ 'except' => null ],
        'status'     => [ 'except' => null ],
        'q'          => [ 'except' => '' ],
    ];

    public function updating($prop)
    {
        // Reset pagination when filters change
        if (in_array($prop, ['date_range','date_from','date_to','courier','status','q'])) {
            $this->resetPage('deptPage');
            $this->resetPage('allPage');
        }
    }

    protected function baseQuery()
    {
        $user = Auth::user();

        $q = Ticket::with(['requester', 'priority', 'status', 'handlingUser', 'receiptUser', 'assignedTo', 'department', 'requestType'])
            ->whereHas('requestType', function ($qq) {
                $qq->where('name', 'Delivery')->orWhere('name', 'Courier');
            });

        if ($user->role !== 'super_admin') {
            $q->where('department_id', $user->department_id);
        }

        // Status filter
        if (!empty($this->status)) {
            $q->whereHas('status', function ($qq) {
                $qq->where('name', $this->status);
            });
        } else {
            // default exclude completed/closed/cancelled
            $q->whereHas('status', function ($qq) {
                $qq->whereNotIn('name', ['Completed', 'Closed', 'Cancelled']);
            });
        }

        // Courier filter
        if (!empty($this->courier)) {
            $q->where('handling_user', $this->courier);
        }

        // Date range presets
        if (!empty($this->date_range) && $this->date_range !== 'custom') {
            switch ($this->date_range) {
                case 'today':
                    $q->where(function ($qq) {
                        $qq->whereDate('schedule_date', today())
                           ->orWhere(function ($qq2) {
                               $qq2->whereNull('schedule_date')->whereDate('created_at', today());
                           });
                    });
                    break;
                case 'this_week':
                    $q->where(function ($qq) {
                        $qq->whereBetween('schedule_date', [now()->startOfWeek(), now()->endOfWeek()])
                           ->orWhere(function ($qq2) {
                               $qq2->whereNull('schedule_date')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                           });
                    });
                    break;
                case 'this_month':
                    $q->where(function ($qq) {
                        $qq->whereDate('schedule_date', '>=', now()->startOfMonth())
                           ->orWhere(function ($qq2) {
                               $qq2->whereNull('schedule_date')->whereDate('created_at', '>=', now()->startOfMonth());
                           });
                    });
                    break;
                case 'last_month':
                    $q->where(function ($qq) {
                        $qq->whereBetween('schedule_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                           ->orWhere(function ($qq2) {
                               $qq2->whereNull('schedule_date')->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
                           });
                    });
                    break;
                // all_time => no filter
            }
        }

        // Custom date range
        if (!empty($this->date_from)) {
            $q->where(function ($qq) {
                $qq->whereDate('schedule_date', '>=', $this->date_from)
                   ->orWhere(function ($qq2) {
                       $qq2->whereNull('schedule_date')->whereDate('created_at', '>=', $this->date_from);
                   });
            });
        }
        if (!empty($this->date_to)) {
            $q->where(function ($qq) {
                $qq->whereDate('schedule_date', '<=', $this->date_to)
                   ->orWhere(function ($qq2) {
                       $qq2->whereNull('schedule_date')->whereDate('created_at', '<=', $this->date_to);
                   });
            });
        }

        // Text search (case-insensitive, robust for JSON fields)
        if (!empty($this->q)) {
            $term = mb_strtolower($this->q);
            $like = "%{$term}%";
            $q->where(function ($qq) use ($like) {
                $qq->whereRaw('LOWER(request_number) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(title) LIKE ?', [$like])
                   ->orWhereRaw('LOWER(COALESCE(handling_user, "")) LIKE ?', [$like])
                   ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, '$.recipient_name'))) LIKE ?", [$like])
                   ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, '$.phone_number'))) LIKE ?", [$like])
                   ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(detail_request, '$.delivery_address'))) LIKE ?", [$like])
                   ->orWhereHas('requester', function ($rq) use ($like) {
                       $rq->whereRaw('LOWER(name) LIKE ?', [$like]);
                   });
            });
        }

        return $q;
    }

    protected function todayOrUnassignedScope($q)
    {
        $user = Auth::user();
        $hasDateFilter = !empty($this->date_from) || !empty($this->date_to) || !empty($this->date_range);
        if ($user->role !== 'super_admin' && !$hasDateFilter) {
            $q->where(function ($qq) {
                $qq->where(function ($qq2) {
                    $qq2->whereDate('schedule_date', today())
                        ->orWhere(function ($qq3) {
                            $qq3->whereNull('schedule_date')->whereDate('created_at', today());
                        });
                })->orWhereNull('receipt_user_id');
            });
        }
        return $q;
    }

    public function render()
    {
        $base = $this->baseQuery();

        // Department tickets (renamed myTickets in old UI) without extra restriction
        $deptTickets = (clone $base)
            ->orderByRaw('CASE WHEN due_date < NOW() THEN 0 ELSE 1 END')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage, ['*'], 'deptPage');

        // All tickets for Today + Unassigned (default) with optional date filters
        $allTicketsQ = $this->todayOrUnassignedScope(clone $base);
        $allTickets = $allTicketsQ
            ->orderByRaw('CASE WHEN due_date < NOW() THEN 0 ELSE 1 END')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage, ['*'], 'allPage');

        // Aggregate counts for cards (based on filtered datasets)
        $deptTotal = $deptTickets->total();
        $allTotal = $allTickets->total();
        $assignedTotal = (clone $allTicketsQ)->whereNotNull('handling_user')->count();
        $pendingTotal = (clone $allTicketsQ)->whereNull('handling_user')->count();

        // For filters
        $allHandlers = Ticket::whereNotNull('handling_user')
            ->whereHas('requestType', function ($q) {
                $q->where('name', 'Delivery')->orWhere('name', 'Courier');
            })
            ->select('handling_user')
            ->distinct()
            ->orderBy('handling_user')
            ->pluck('handling_user');

        $statuses = \App\Models\RequestStatus::all();

        return view('livewire.courier.today-tickets', [
            'deptTickets' => $deptTickets,
            'allTickets' => $allTickets,
            'deptTotal' => $deptTotal,
            'allTotal' => $allTotal,
            'assignedTotal' => $assignedTotal,
            'pendingTotal' => $pendingTotal,
            'allHandlers' => $allHandlers,
            'statuses' => $statuses,
        ]);
    }
}
