<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriorityController extends Controller
{
    private function checkSuperAdmin()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can manage priority settings.');
        }
    }

    /**
     * Display a listing of the priorities.
     */
    public function index()
    {
        $this->checkSuperAdmin();

        $priorities = Priority::orderBy('id')->get();
        return view('admin.priorities.index', compact('priorities'));
    }

    /**
     * Show the form for editing the specified priority.
     */
    public function edit(Priority $priority)
    {
        $this->checkSuperAdmin();

        return view('admin.priorities.edit', compact('priority'));
    }

    /**
     * Update the specified priority in storage.
     */
    public function update(Request $request, Priority $priority)
    {
        $this->checkSuperAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'sla_response_hours' => 'required|integer|min:1|max:168', // Max 1 week
            'sla_resolution_hours' => 'required|integer|min:1|max:720', // Max 30 days
            'color' => 'required|in:danger,warning,info,success,primary,secondary',
            'icon' => 'required|string|max:50',
        ]);

        // Ensure resolution time >= response time
        if ($validated['sla_resolution_hours'] < $validated['sla_response_hours']) {
            return back()->withErrors(['sla_resolution_hours' => 'Resolution time must be greater than or equal to response time.'])
                ->withInput();
        }

        $priority->update($validated);

        return redirect()->route('admin.priorities.index')
            ->with('success', "Priority '{$priority->name}' updated successfully!");
    }
}
