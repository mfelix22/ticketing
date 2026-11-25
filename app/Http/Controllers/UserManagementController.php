<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is super admin
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can access user management.');
        }

        $query = User::with('department');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can create users.');
        }

        $departments = Department::all();
        return view('admin.users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            // Accept lowercase values from the form
            'status' => 'nullable|in:requester,approver,both',
            'is_pic' => 'boolean',
            'role' => 'required|in:user,pic,admin,super_admin,manager,operations_manager,general_manager',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_pic'] = $request->has('is_pic');

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'company' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'nullable|in:requester,approver,both',
            'is_pic' => 'boolean',
            'role' => 'required|in:user,pic,admin,super_admin,manager,operations_manager,general_manager',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_pic'] = $request->has('is_pic');

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
