<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Allow super admin, operations manager, and BC department users to manage customers
        $user = Auth::user();
        if (!$user->canManageCustomers()) {
            return redirect()->route('dashboard')
                ->with('error', 'Only Super Admin, Operations Manager, and BC Department users can manage customers.');
        }

        $query = Customer::query();
        
        // Search by customer name or code
        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('code', 'like', $like)
                  ->orWhere('pic_name', 'like', $like)
                  ->orWhere('phone', 'like', $like);
            });
        }
        
        $customers = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->canManageCustomers()) {
            return redirect()->route('dashboard')
                ->with('error', 'Only Super Admin, Operations Manager, and BC Department users can manage customers.');
        }

        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageCustomers()) {
            return redirect()->route('dashboard')
                ->with('error', 'Only Super Admin, Operations Manager, and BC Department users can manage customers.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:customers,code',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $user = Auth::user();
        if (!$user->canManageCustomers()) {
            return redirect()->route('dashboard')
                ->with('error', 'Only Super Admin, Operations Manager, and BC Department users can manage customers.');
        }

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $user = Auth::user();
        if (!$user->canManageCustomers()) {
            return redirect()->route('dashboard')
                ->with('error', 'Only Super Admin, Operations Manager, and BC Department users can manage customers.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pic_name' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50|unique:customers,code,' . $customer->id,
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Only Super Admin can manage customers.');
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
