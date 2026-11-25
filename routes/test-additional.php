<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/test-login/{username}/{password}', function ($username, $password) {
    $html = "<h3>Testing Login Process for: {$username}</h3>";

    // Find user
    $user = User::where('username', $username)->first();
    if (!$user) {
        $html .= "<p style='color: red;'>User '{$username}' not found!</p>";
        return $html;
    }

    $html .= "<p style='color: green;'>User found: {$user->name}</p>";

    // Check password
    $passwordCheck = Hash::check($password, $user->password);
    $html .= "<p>Password check: " . ($passwordCheck ? '<span style="color: green;">PASS</span>' : '<span style="color: red;">FAIL</span>') . "</p>";

    if ($passwordCheck) {
        // Try to login
        Auth::login($user);
        if (Auth::check()) {
            $html .= "<p style='color: green;'>Login successful! Currently logged in as: " . Auth::user()->name . "</p>";
            $html .= "<p><a href='/dashboard'>Go to Dashboard</a></p>";
        } else {
            $html .= "<p style='color: red;'>Login failed - Auth::check() returned false</p>";
        }
    }

    return $html;
});
