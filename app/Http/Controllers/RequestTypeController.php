<?php

namespace App\Http\Controllers;

use App\Models\RequestType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestTypeController extends Controller
{
    public function index()
    {
        if (!Auth::user()->canManageRequestTypes()) {
            abort(403, 'Only Super Admins can manage request types.');
        }

        $requestTypes = RequestType::withCount('tickets')->paginate(15);
        return view('admin.request-types.index', compact('requestTypes'));
    }

    public function create()
    {
        if (!Auth::user()->canManageRequestTypes()) {
            abort(403);
        }

        return view('admin.request-types.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->canManageRequestTypes()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:request_types',
            'description' => 'nullable|string',
        ]);

        RequestType::create($validated);

        return redirect()->route('request-types.index')->with('success', 'Request Type created successfully.');
    }

    public function edit(RequestType $requestType)
    {
        if (!Auth::user()->canManageRequestTypes()) {
            abort(403);
        }

        $requestType->load('fields');

        return view('admin.request-types.edit', compact('requestType'));
    }

    public function update(Request $request, RequestType $requestType)
    {
        if (!Auth::user()->canManageRequestTypes()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:request_types,name,' . $requestType->id,
            'description' => 'nullable|string',
        ]);

        $requestType->update($validated);

        return redirect()->route('request-types.index')->with('success', 'Request Type updated successfully.');
    }

    public function destroy(RequestType $requestType)
    {
        if (!Auth::user()->canManageRequestTypes()) {
            abort(403);
        }

        $requestType->delete();

        return redirect()->route('request-types.index')->with('success', 'Request Type deleted successfully.');
    }
}
