<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketTemplate;
use App\Models\RequestType;
use App\Models\Department;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = TicketTemplate::with(['requestType', 'department', 'defaultPriority', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $requestTypes = RequestType::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $priorities = Priority::orderBy('name')->get();

        return view('admin.templates.create', compact('requestTypes', 'departments', 'priorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'request_type_id' => 'required|exists:request_types,id',
            'department_id' => 'nullable|exists:departments,id',
            'title_template' => 'required|string|max:255',
            'default_priority_id' => 'nullable|exists:priorities,id',
            'field_defaults' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Debug auth
        $user = Auth::user();
        Log::info('Template store auth debug', [
            'user' => $user ? $user->toArray() : null,
            'auth_id' => Auth::id(),
            'auth_check' => Auth::check(),
        ]);

        if (!$user || !$user->id) {
            Log::error('Template store: User not authenticated or missing ID');
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $validated['created_by'] = (int) $user->id;
        $validated['is_active'] = $request->has('is_active');
        Log::info('Template store payload', ['user_id' => $user->id, 'validated' => $validated]);
        
        TicketTemplate::create($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TicketTemplate $template)
    {
        $requestTypes = RequestType::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $priorities = Priority::orderBy('name')->get();

        return view('admin.templates.edit', compact('template', 'requestTypes', 'departments', 'priorities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TicketTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'request_type_id' => 'required|exists:request_types,id',
            'department_id' => 'nullable|exists:departments,id',
            'title_template' => 'required|string|max:255',
            'default_priority_id' => 'nullable|exists:priorities,id',
            'field_defaults' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        Log::info('Template update payload', [
            'template_id' => $template->id,
            'validated' => $validated,
        ]);
        $template->update($validated);

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template deleted successfully!');
    }

    /**
     * Get templates for a specific request type (AJAX)
     */
    public function getByRequestType(Request $request)
    {
        try {
            $requestTypeId = $request->input('request_type_id');

            if (!$requestTypeId) {
                return response()->json([]);
            }

            $templates = TicketTemplate::active()
                ->forRequestType($requestTypeId)
                ->with('defaultPriority')
                ->get();

            return response()->json($templates);
        } catch (\Exception $e) {
            \Log::error('Error fetching templates: ' . $e->getMessage());
            return response()->json([]);
        }
    }
}
