<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ticketing System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
        }

        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px;
        }

        .login-header {
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
            border-radius: 20px 20px 0 0;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-login {
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(90deg, #0056b3 0%, #004494 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }

        .input-with-icon {
            padding-left: 48px;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4" x-data="{ loading: false }">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-20 h-20 bg-white opacity-10 rounded-full floating-animation"></div>
        <div class="absolute top-32 right-20 w-16 h-16 bg-white opacity-10 rounded-full floating-animation"
            style="animation-delay: -2s;"></div>
        <div class="absolute bottom-20 left-32 w-24 h-24 bg-white opacity-10 rounded-full floating-animation"
            style="animation-delay: -4s;"></div>
        <div class="absolute bottom-32 right-10 w-12 h-12 bg-white opacity-10 rounded-full floating-animation"
            style="animation-delay: -1s;"></div>
    </div>

    <div class="login-card w-full max-w-md">
        <!-- Header -->
        <div class="login-header text-center py-6 text-white">
            <div class="mb-4">
                <i class="fas fa-ticket-alt text-4xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Ticketing System</h1>
            <p class="text-blue-100 mt-2">Welcome back! Please sign in with your username</p>
        </div>

        <!-- Form Content -->
        <div class="p-8">
            {{ $slot }}
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>
