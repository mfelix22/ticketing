<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        if (!Auth::user()->canManageDepartments()) {
            abort(403, 'Only Super Admins can manage departments.');
        }

        $departments = Department::withCount('tickets')->paginate(15);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        if (!Auth::user()->canManageDepartments()) {
            abort(403);
        }

        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->canManageDepartments()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        if (!Auth::user()->canManageDepartments()) {
            abort(403);
        }

        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        if (!Auth::user()->canManageDepartments()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if (!Auth::user()->canManageDepartments()) {
            abort(403);
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
