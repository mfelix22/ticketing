<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    try {
        \Log::info('=== LOGIN ATTEMPT STARTED ===');
        \Log::info('Username: ' . $this->form->username);
        \Log::info('Password length: ' . strlen($this->form->password));
        \Log::info('Remember: ' . ($this->form->remember ? 'true' : 'false'));

        $this->validate();
        \Log::info('Validation passed');

        $this->form->authenticate();
        \Log::info('Authentication method completed');

        // Check if user is actually logged in
        if (\Auth::check()) {
            \Log::info('User is authenticated: ' . \Auth::user()->name);
            \Log::info('User ID: ' . \Auth::user()->id);
        } else {
            \Log::error('User is NOT authenticated after authenticate() call');
        }

        Session::regenerate();
        \Log::info('Session regenerated');

        // Try different redirect approaches
        \Log::info('About to redirect to dashboard');

        // Perform a full-page redirect to the intended/dashboard route
        return redirect()->intended(route('dashboard', absolute: false));
        \Log::info('Redirect command issued');
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error: ' . $e->getMessage());
        \Log::error('Validation errors: ' . json_encode($e->errors()));
        throw $e;
    } catch (\Exception $e) {
        \Log::error('Login error: ' . $e->getMessage());
        \Log::error('Error type: ' . get_class($e));
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        throw $e;
    }
};

?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit.prevent="login" method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf
        <!-- Username -->
        <div class="input-group">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user mr-2"></i>Username
            </label>
            <div class="relative">
                <i class="fas fa-user input-icon"></i>
                <input wire:model="form.username" id="username" class="form-control input-with-icon w-full"
                    type="text" name="username" placeholder="Enter your username" required autofocus
                    autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('form.username')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Password -->
        <div class="input-group">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-lock mr-2"></i>Password
            </label>
            <div class="relative">
                <i class="fas fa-lock input-icon"></i>
                <input wire:model="form.password" id="password" class="form-control input-with-icon w-full"
                    type="password" name="password" placeholder="Enter your password" required
                    autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-red-600 text-sm" />
        </div>

        <!-- Remember Me -->
        {{-- <div class="flex items-center justify-between">
            <label for="remember" class="flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 hover:text-blue-800 hover:underline"
                    href="{{ route('password.request') }}" wire:navigate>
                    Forgot password?
                </a>
            @endif
        </div> --}}

        <!-- Login Button -->
        <button type="submit"
            class="btn-login w-full text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center"
            wire:loading.attr="disabled">
            <span wire:loading.remove>
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </span>
            <span wire:loading class="flex items-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Signing in...
            </span>
        </button>
    </form>
</div>
