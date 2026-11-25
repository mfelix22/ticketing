<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\RequestStatus;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TicketReportExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Block IT department users from the general report
        if (auth()->user()->department_id == 1) {
            abort(403, 'Unauthorized');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $departmentId = $request->input('department_id');
        $statusId = $request->input('status_id');
        
        // Build the query
        $query = Ticket::with([
            'requester',
            'requestType',
            'status',
            'department'
        ]);
        
        // Apply date filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('schedule_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        
        // Apply department filter if provided
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        // Apply status filter if provided
        if ($statusId) {
            $query->where('status_id', $statusId);
        }
        
        // Get paginated tickets (20 per page)
        $tickets = $query->orderBy('schedule_date', 'desc')->paginate(20);
        
        // Calculate totals from all filtered records for summary cards
        // Rebuild the same query without pagination
        $totalQuery = Ticket::with([
            'requester',
            'requestType',
            'status',
            'department'
        ]);
        
        // Apply the same filters
        if ($startDate && $endDate) {
            $totalQuery->whereBetween('schedule_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        
        if ($departmentId) {
            $totalQuery->where('department_id', $departmentId);
        }
        
        if ($statusId) {
            $totalQuery->where('status_id', $statusId);
        }
        
        $allTickets = $totalQuery->get();
        $totalParkir = 0; // For summary cards (all tickets)
        $totalLuarKota = 0; // For summary cards (all tickets)
        
        foreach ($allTickets as $ticket) {
            // Get costs from database fields - handle string values properly
            $parkir = !empty($ticket->expenses_value) ? (float) $ticket->expenses_value : 0;
            $luarKota = !empty($ticket->other_costs) ? (float) $ticket->other_costs : 0;
            
            $totalParkir += $parkir;
            $totalLuarKota += $luarKota;
        }
        
        // Calculate totals for current page only (for table footer)
        $pageParkir = 0;
        $pageLuarKota = 0;
        
        foreach ($tickets as $ticket) {
            $parkir = !empty($ticket->expenses_value) ? (float) $ticket->expenses_value : 0;
            $luarKota = !empty($ticket->other_costs) ? (float) $ticket->other_costs : 0;
            
            $pageParkir += $parkir;
            $pageLuarKota += $luarKota;
        }
        
        // Get filter options
        $departments = Department::all();
        $statuses = RequestStatus::all();
        
        return view('reports.index', compact(
            'tickets',
            'startDate',
            'endDate',
            'departmentId',
            'statusId',
            'totalParkir',
            'totalLuarKota',
            'pageParkir',
            'pageLuarKota',
            'departments',
            'statuses'
        ));
    }
    
    public function export(Request $request)
    {
        // Block IT department users from the general report export
        if (auth()->user()->department_id == 1) {
            abort(403, 'Unauthorized');
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $departmentId = $request->input('department_id');
        $statusId = $request->input('status_id');
        
        $query = Ticket::with([
            'requester',
            'requestType',
            'status',
            'department'
        ]);
        
        if ($startDate && $endDate) {
            $query->whereBetween('schedule_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        if ($statusId) {
            $query->where('status_id', $statusId);
        }
        
        $tickets = $query->orderBy('schedule_date', 'desc')->get();
        
        $filename = 'laporan_ticketing_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new TicketReportExport($tickets), $filename);
    }

    /**
     * IT Department Only Report
     */
    public function it(Request $request)
    {
        $user = auth()->user();
        // Only allow IT department users
        if (!$user || $user->department_id != 1) {
            abort(403, 'Unauthorized');
        }
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusId = $request->input('status_id');
        $requesterId = $request->input('requester_id');
        $query = \App\Models\Ticket::with(['requester', 'priority', 'status'])
            ->where('department_id', 1);
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay()
            ]);
        }
        if ($statusId) {
            $query->where('status_id', $statusId);
        }
        if ($requesterId) {
            $query->where('requester_id', $requesterId);
        }
        $tickets = $query->orderByDesc('created_at')->paginate(20);
        $statuses = \App\Models\RequestStatus::all();
        $requesters = \App\Models\User::whereHas('requestedTickets', function ($q) {
            $q->where('department_id', 1);
        })->orderBy('name')->get();
        return view('reports.it', [
            'tickets' => $tickets,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'statusId' => $statusId,
            'requesterId' => $requesterId,
            'requesters' => $requesters,
            'statuses' => $statuses,
        ]);
    }

    public function itExport(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->department_id != 1) {
            abort(403, 'Unauthorized');
        }
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $statusId = $request->input('status_id');
        $requesterId = $request->input('requester_id');
        $query = \App\Models\Ticket::with(['requester', 'priority', 'status'])
            ->where('department_id', 1);
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($startDate)->startOfDay(),
                \Carbon\Carbon::parse($endDate)->endOfDay()
            ]);
        }
        if ($statusId) {
            $query->where('status_id', $statusId);
        }
        if ($requesterId) {
            $query->where('requester_id', $requesterId);
        }
        $tickets = $query->orderByDesc('created_at')->get();
        $filename = 'it_ticket_report_' . date('Y-m-d_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ITTicketReportExport($tickets), $filename);
    }
}
