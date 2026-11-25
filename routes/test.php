<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Route::get('/create-test-user', function () {
    // Delete existing user if exists
    User::where('username', 'admin')->delete();

    $user = User::create([
        'name' => 'Admin User',
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'is_pic' => true,
    ]);

    return "Test user created!<br>Username: admin<br>Password: password<br><a href='/login'>Login here</a>";
});

Route::get('/create-regular-user', function () {
    // Delete existing user if exists
    User::where('username', 'user')->delete();

    $user = User::create([
        'name' => 'Regular User',
        'username' => 'user',
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'is_pic' => false,
    ]);

    return "Regular user created!<br>Username: user<br>Password: password<br><a href='/login'>Login here</a>";
});

Route::get('/debug-auth', function () {
    $users = User::all(['id', 'name', 'username', 'email']);
    $html = "<h3>Users in database:</h3>";
    foreach ($users as $user) {
        $html .= "ID: {$user->id}, Name: {$user->name}, Username: {$user->username}, Email: {$user->email}<br>";
    }

    // Test password verification
    $testUser = User::where('username', 'admin')->first();
    if ($testUser) {
        $passwordCheck = Hash::check('password', $testUser->password);
        $html .= "<br>Password check for admin user: " . ($passwordCheck ? 'PASS' : 'FAIL');

        // Test manual login
        $html .= "<br><br><strong>Testing manual login...</strong><br>";
        if (Auth::attempt(['username' => 'admin', 'password' => 'password'])) {
            $html .= "Auth::attempt with username: PASS<br>";
            Auth::logout();
        } else {
            $html .= "Auth::attempt with username: FAIL<br>";
        }

        // Try with email field
        if (Auth::attempt(['email' => $testUser->email, 'password' => 'password'])) {
            $html .= "Auth::attempt with email: PASS<br>";
            Auth::logout();
        } else {
            $html .= "Auth::attempt with email: FAIL<br>";
        }
    }

    return $html;
});

Route::get('/simple-debug', function () {
    // First, show what users exist
    $users = User::all(['id', 'name', 'username', 'email']);
    $html = "<h3>All Users in Database:</h3>";

    if ($users->count() == 0) {
        $html .= "<p style='color: red;'>NO USERS FOUND IN DATABASE!</p>";
    } else {
        foreach ($users as $user) {
            $html .= "ID: {$user->id}, Name: {$user->name}, Username: '{$user->username}', Email: {$user->email}<br>";
        }
    }

    // Test direct authentication with hardcoded values
    $html .= "<hr><h3>Testing Direct Authentication:</h3>";

    // Test with admin
    $adminUser = User::where('username', 'admin')->first();
    if ($adminUser) {
        $html .= "Admin user found: {$adminUser->name}<br>";
        $passwordTest = Hash::check('password', $adminUser->password);
        $html .= "Password test for 'admin' with 'password': " . ($passwordTest ? '<span style="color: green;">PASS</span>' : '<span style="color: red;">FAIL</span>') . "<br>";
    } else {
        $html .= "<span style='color: red;'>Admin user NOT found</span><br>";
    }

    // Test with user
    $regularUser = User::where('username', 'user')->first();
    if ($regularUser) {
        $html .= "Regular user found: {$regularUser->name}<br>";
        $passwordTest = Hash::check('password', $regularUser->password);
        $html .= "Password test for 'user' with 'password': " . ($passwordTest ? '<span style="color: green;">PASS</span>' : '<span style="color: red;">FAIL</span>') . "<br>";
    } else {
        $html .= "<span style='color: red;'>Regular user NOT found</span><br>";
    }

    return $html;
});

Route::get('/force-login', function () {
    // Clear all caches first
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');

    $html = "<h3>Force Login Test</h3>";

    // Check if user exists
    $user = User::where('username', 'admin')->first();
    if (!$user) {
        $html .= "<p style='color: red;'>No admin user found. <a href='/create-test-user'>Create one first</a></p>";
        return $html;
    }

    $html .= "<p>User found: {$user->name} (ID: {$user->id})</p>";

    // Check current auth state
    if (Auth::check()) {
        $html .= "<p>Currently logged in as: " . Auth::user()->name . "</p>";
        $html .= "<p><a href='/dashboard'>Go to Dashboard</a> | <a href='/logout'>Logout</a></p>";
    } else {
        $html .= "<p>Not currently logged in</p>";

        // Force login
        Auth::login($user);

        if (Auth::check()) {
            $html .= "<p style='color: green;'>Force login successful!</p>";
            $html .= "<p>Now logged in as: " . Auth::user()->name . "</p>";
            $html .= "<p><a href='/dashboard'>Go to Dashboard</a></p>";
        } else {
            $html .= "<p style='color: red;'>Force login failed!</p>";
        }
    }

    return $html;
});

Route::get('/auto-login/{username}', function ($username) {
    // Find user
    $user = User::where('username', $username)->first();

    if (!$user) {
        return "User '{$username}' not found! <a href='/create-test-user'>Create test user</a>";
    }

    // Log the user in
    Auth::login($user);

    if (Auth::check()) {
        // Redirect to dashboard
        return redirect()->route('dashboard');
    } else {
        return "Login failed for user: {$username}";
    }
});

Route::get('/auth-status', function () {
    $html = "<h3>Authentication Status</h3>";

    if (Auth::check()) {
        $user = Auth::user();
        $html .= "<p style='color: green;'>✅ User is logged in</p>";
        $html .= "<p><strong>User:</strong> {$user->name} (ID: {$user->id})</p>";
        $html .= "<p><strong>Username:</strong> {$user->username}</p>";
        $html .= "<p><strong>Email:</strong> {$user->email}</p>";
        $html .= "<p><a href='/dashboard'>Go to Dashboard</a></p>";
        $html .= "<p><a href='/logout'>Logout</a></p>";
    } else {
        $html .= "<p style='color: red;'>❌ No user is logged in</p>";
        $html .= "<p><a href='/login'>Go to Login</a></p>";
        $html .= "<p><a href='/auto-login/admin'>Auto Login as Admin</a></p>";
    }

    // Show session info
    $html .= "<hr><h4>Session Info:</h4>";
    $html .= "<p>Session ID: " . session()->getId() . "</p>";
    $html .= "<p>Session Driver: " . config('session.driver') . "</p>";

    return $html;
});
