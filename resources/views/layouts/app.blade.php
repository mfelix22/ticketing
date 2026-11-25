<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ticketing System') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        .admin-wrapper {
            min-height: 100vh;
            background-color: #f4f6f9;
        }

        .main-header {
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .main-sidebar {
            background: #ffffff;
            box-shadow: 2px 0 6px rgba(0, 0, 0, .1);
            border-right: 1px solid #e5e7eb;
        }

        .content-wrapper {
            background-color: #f4f6f9;
            min-height: calc(100vh - 57px);
        }

        /* Override when using custom wrapper */
        .content-wrapper[style*="margin-left"] {
            margin-left: 250px !important;
        }

        /* Override any dark theme styling */
        .content-wrapper .content {
            background-color: transparent;
        }

        .content-wrapper .content-header {
            background-color: #f4f6f9;
        }

        /* Ensure form elements have light background */
        .form-control,
        .form-select,
        .custom-file-label {
            background-color: #fff !important;
            color: #495057 !important;
            border: 1px solid #ced4da !important;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff !important;
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }

        /* Card styling */
        .card {
            background-color: #fff !important;
            color: #495057 !important;
        }

        .card-header {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
            color: #495057 !important;
        }

        .card-body {
            background-color: #fff !important;
            color: #495057 !important;
        }

        .card-footer {
            background-color: #f8f9fa !important;
            border-top: 1px solid #dee2e6 !important;
        }

        /* Labels and text */
        label,
        .form-text {
            color: #495057 !important;
        }

        /* Only apply to form elements, not all paragraphs */
        .card-body p,
        .card-body span:not(.badge) {
            color: #495057;
        }

        /* Breadcrumb styling */
        .breadcrumb {
            background-color: transparent;
        }

        .breadcrumb-item a {
            color: #007bff;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }

        .sidebar-menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-menu>li>a {
            display: block;
            padding: 12px 15px;
            color: #4b5563;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu>li>a:hover,
        .sidebar-menu>li.active>a {
            background-color: #007bff;
            color: #fff;
        }

        .sidebar-menu>li>a i {
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .sidebar-menu>li>a:hover i,
        .sidebar-menu>li.active>a i {
            color: #fff;
        }

        .content-header {
            padding: 20px;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }

        .card {
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
            border: none;
            border-radius: 0.25rem;
        }

        .btn-primary {
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #0056b3 0%, #004494 100%);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
            }

            .main-sidebar {
                position: fixed;
                top: 57px;
                left: -250px;
                transition: left 0.3s ease;
                z-index: 1000;
                height: calc(100vh - 57px);
            }

            .main-sidebar.show {
                left: 0;
            }
        }
    </style>
</head>

<body class="admin-wrapper" x-data="{ sidebarOpen: false }">
    <!-- Main Header -->
    <nav
        class="main-header fixed top-0 left-0 right-0 z-50 h-14 bg-blue-600 text-white flex items-center justify-between px-4 shadow-md">
        <!-- Left side -->
        <div class="flex items-center">
            <!-- Sidebar toggle -->
            <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-white hover:bg-blue-700 p-2 rounded">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('dashboard') }}"
                class="flex items-center ml-2 text-white hover:text-blue-100 transition-colors">
                <i class="fas fa-ticket-alt text-2xl mr-2 text-white"></i>
                <span class="font-bold text-lg text-white">Ticketing System</span>
            </a>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-2">
            <!-- Notifications Bell -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="relative hover:bg-blue-700 px-3 py-2 rounded-lg transition-all duration-200">
                    <i class="fas fa-bell text-xl text-white notification-bell"></i>
                    @if (Auth::user()->unreadNotifications->count() > 0)
                        <span
                            class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center">
                            {{ Auth::user()->unreadNotifications->count() }}
                        </span>
                    @else
                        <span
                            class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[1.25rem] text-center"
                            style="display: none;">0</span>
                    @endif
                </button>

                <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-3 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-50 overflow-hidden"
                    style="display: none;">

                    <!-- Notification Header -->
                    <div
                        class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-bell text-blue-600"></i>
                            <h3 class="text-base font-semibold text-gray-900">Notifications</h3>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <span class="bg-blue-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </div>
                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline transition-all">
                                    Mark all read
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Notifications List -->
                    <div class="max-h-96 overflow-y-auto">
                        @forelse(Auth::user()->unreadNotifications->take(10) as $notification)
                            <a href="{{ $notification->data['url'] ?? '#' }}?notification={{ $notification->id }}"
                                class="block px-4 py-3 hover:bg-gray-50 transition-all duration-150 border-b border-gray-100 last:border-0 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50 border-l-4 border-l-blue-500' }}">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0">
                                        @if (isset($notification->data['hours_overdue']))
                                            <div
                                                class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                            </div>
                                        @elseif (isset($notification->data['comment_preview']))
                                            <div
                                                class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-comment text-green-600 text-sm"></i>
                                            </div>
                                        @elseif (isset($notification->data['old_status']))
                                            <div
                                                class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-sync-alt text-purple-600 text-sm"></i>
                                            </div>
                                        @else
                                            <div
                                                class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-bell text-blue-600 text-sm"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900 {{ $notification->read_at ? 'text-gray-700' : 'text-gray-900' }}">
                                            {{ $notification->data['message'] ?? 'New notification' }}
                                        </p>
                                        @if (isset($notification->data['request_number']) || isset($notification->data['title']))
                                            <p class="text-xs text-gray-600 mt-1 truncate font-medium">
                                                @if (isset($notification->data['request_number']))
                                                    {{ $notification->data['request_number'] }}:
                                                @endif
                                                {{ $notification->data['title'] ?? '' }}
                                            </p>
                                        @endif
                                        @if (isset($notification->data['comment_preview']))
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                "{{ Str::limit($notification->data['comment_preview'], 60) }}"
                                            </p>
                                        @endif
                                        <div class="flex items-center mt-1.5 text-xs text-gray-500">
                                            <i class="far fa-clock mr-1"></i>
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    @if (!$notification->read_at)
                                        <div class="flex-shrink-0">
                                            <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-12 text-center text-gray-500">
                                <i class="fas fa-bell-slash text-5xl mb-3 text-gray-300"></i>
                                <p class="text-sm font-medium">No notifications yet</p>
                                <p class="text-xs text-gray-400 mt-1">We'll notify you when something happens</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- View All Link -->
                    @if (Auth::user()->unreadNotifications->count() > 0)
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                            <a href="{{ route('notifications.index') }}"
                                class="flex items-center justify-center text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline transition-all">
                                <span>View all notifications</span>
                                <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="flex items-center hover:bg-blue-700 px-3 py-2 rounded transition-colors">
                    <i class="fas fa-user-circle text-xl mr-2 text-white"></i>
                    <span class="text-white font-medium">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down ml-2 text-sm transition-transform text-white"
                        :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50" style="display: none;">

                    {{-- <a href="{{ route('profile') }}"
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-user mr-2"></i>
                        Profile
                    </a> --}}

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Sidebar -->
    <aside
        class="main-sidebar fixed top-14 left-0 z-40 w-64 bg-white text-gray-700 transform transition-transform md:translate-x-0 border-r border-gray-200"
        style="height: calc(100vh - 3.5rem); overflow-y: auto; min-height: 600px;"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="p-4 pb-20" style="min-height: 100%;">
            <ul class="sidebar-menu space-y-1">
                <!-- Dashboard -->
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                        <i
                            class="fas fa-tachometer-alt w-6 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-600' }}"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                </li>

                <!-- Notifications -->
                {{-- <li class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <a href="{{ route('notifications.index') }}"
                        class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('notifications.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                        <i
                            class="fas fa-bell w-6 {{ request()->routeIs('notifications.*') ? 'text-white' : 'text-gray-600' }}"></i>
                        <span class="ml-3">Notifications</span>
                        @if (Auth::user()->unreadNotifications->count() > 0)
                            <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>
                </li> --}}

                @if (Auth::user()->status !== 'Approver')
                    <!-- My Tickets (Hidden for Approver-only users) -->
                    <li class="{{ request()->routeIs('tickets.index') ? 'active' : '' }}">
                        <a href="{{ route('tickets.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('tickets.index') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-ticket-alt w-6 {{ request()->routeIs('tickets.index') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3">My Tickets</span>
                        </a>
                    </li>

                    <!-- Create Ticket (Hidden for Approver-only users) -->
                    <li class="{{ request()->routeIs('tickets.create') ? 'active' : '' }}">
                        <a href="{{ route('tickets.create') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('tickets.create') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-plus w-6 flex-shrink-0 {{ request()->routeIs('tickets.create') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Create Ticket</span>
                        </a>
                    </li>
                @endif

                @php
                    $isPurchasingManager = Auth::user()->role === 'manager' && Auth::user()->department && Auth::user()->department->name === 'Purchasing';
                @endphp
                @if (!$isPurchasingManager && in_array(Auth::user()->status, ['Requester', 'Approver', 'Both']) && Auth::user()->department && in_array(Auth::user()->department->name, ['IT', 'Purchasing', 'Billing Collection']))
                    <!-- Department Delivery Packages (Only for Requester/Approver in IT, Purchasing, BC) -->
                    <li class="{{ request()->routeIs('courier.today') ? 'active' : '' }}">
                        <a href="{{ route('courier.today') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('courier.today') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-shipping-fast w-6 {{ request()->routeIs('courier.today') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3">Department Delivery Packages</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->role === 'operations_manager' || Auth::user()->is_pic == 1)
                    <!-- Reports Dropdown -->
                    <li x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <button @click="open = !open"
                            class="flex items-center w-full p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-chart-bar w-6 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3">Reports</span>
                            <i class="fas fa-chevron-down ml-auto transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <ul x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1" class="ml-6 mt-2 space-y-1">
                            @if (Auth::user()->department_id != 1)
                                <li>
                                    <a href="{{ route('reports.index') }}"
                                        class="flex items-center p-2 pl-4 rounded-lg hover:bg-blue-600 hover:text-white transition-colors text-sm {{ request()->routeIs('reports.index') ? 'bg-blue-500 text-white' : 'text-gray-600' }}">
                                        <i class="fas fa-file-alt w-5 text-xs"></i>
                                        <span class="ml-2">Laporan Ticketing</span>
                                    </a>
                                </li>
                            @endif
                            @if (Auth::user()->department_id == 1)
                                <li>
                                    <a href="{{ route('reports.it') }}"
                                        class="flex items-center p-2 pl-4 rounded-lg hover:bg-blue-600 hover:text-white transition-colors text-sm {{ request()->routeIs('reports.it') ? 'bg-blue-500 text-white' : 'text-gray-600' }}">
                                        <i class="fas fa-table w-5 text-xs"></i>
                                        <span class="ml-2">IT Report</span>
                                    </a>
                                </li>
                            @endif
                            <!-- Add more report items here in the future -->
                        </ul>
                    </li>
                @endif

                @if (Auth::user()->is_pic || in_array(Auth::user()->status, ['Approver', 'Both']))
                    <!-- Assigned Tickets (Only for Approvers, PICs, and Both) -->
                    <li class="{{ request()->routeIs('tickets.assigned') ? 'active' : '' }}">
                        <a href="{{ route('tickets.assigned') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('tickets.assigned') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-user-check w-6 {{ request()->routeIs('tickets.assigned') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3">Assigned to Me</span>
                        </a>
                    </li>

                    <!-- Delivery Packages Report (PICs and Admins) -->
                    {{--
                    <li class="{{ request()->routeIs('courier.*') ? 'active' : '' }}">
                        <a href="{{ route('courier.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('courier.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-shipping-fast w-6 {{ request()->routeIs('courier.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3">Packages Report</span>
                        </a>
                    </li>
                    --}}
                @endif

                @if (in_array(Auth::user()->role, ['manager', 'operations_manager']))
                    <!-- Department Tickets (Manager view of all department tickets) -->
                    <li class="{{ request()->routeIs('tickets.department') ? 'active' : '' }}">
                        <a href="{{ route('tickets.department') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('tickets.department') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-users w-6 {{ request()->routeIs('tickets.department') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3">Department Tickets</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->isSuperAdmin())
                    <!-- Super Admin Section -->
                    <li class="pt-4">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            Super Admin
                        </div>
                    </li>

                    <!-- User Management -->
                    <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-users w-6 flex-shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">User Management</span>
                        </a>
                    </li>

                    <!-- Customer Management (Super Admin) -->
                    <li class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.customers.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('admin.customers.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-building w-6 flex-shrink-0 {{ request()->routeIs('admin.customers.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Manage Customer</span>
                        </a>
                    </li>

                    <!-- Departments -->
                    <li class="{{ request()->routeIs('departments.*') ? 'active' : '' }}">
                        <a href="{{ route('departments.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('departments.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-building w-6 flex-shrink-0 {{ request()->routeIs('departments.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Departments</span>
                        </a>
                    </li>

                    <!-- Request Types -->
                    <li class="{{ request()->routeIs('request-types.*') ? 'active' : '' }}">
                        <a href="{{ route('request-types.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('request-types.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-list w-6 flex-shrink-0 {{ request()->routeIs('request-types.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Request Types</span>
                        </a>
                    </li>

                    <!-- Priority SLA Management -->
                    <li class="{{ request()->routeIs('admin.priorities.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.priorities.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('admin.priorities.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-sliders-h w-6 flex-shrink-0 {{ request()->routeIs('admin.priorities.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Priority SLA</span>
                        </a>
                    </li>

                    <!-- Ticket Templates -->
                    <li class="{{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.templates.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('admin.templates.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-layer-group w-6 flex-shrink-0 {{ request()->routeIs('admin.templates.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Ticket Templates</span>
                        </a>
                    </li>
                @elseif (Auth::user()->canManageCustomers())
                    <!-- Customer Management (Billing Collection Department or Operations Manager) -->
                    <li class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.customers.index') }}"
                            class="flex items-center p-3 rounded-lg hover:bg-blue-600 hover:text-white transition-colors {{ request()->routeIs('admin.customers.*') ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                            <i
                                class="fas fa-building w-6 flex-shrink-0 {{ request()->routeIs('admin.customers.*') ? 'text-white' : 'text-gray-600' }}"></i>
                            <span class="ml-3 whitespace-nowrap">Manage Customer</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper md:ml-64 pt-14">
        <!-- Content Header - Only show if header has actual content -->
        @if (isset($header) && trim($header) != '' && !str_contains($header, '<!-- Empty'))
            <div class="content-header bg-white border-b border-gray-200 mb-6">
                <div class="px-6 py-4">
                    {{ $header }}
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <main class="w-full pb-6">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
    </div>

    <!-- Bootstrap Bundle with Popper (required for modals, tooltips, etc.) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto-refresh notifications script -->
    <script>
        // Store the last notification count
        let lastNotificationCount = {{ Auth::user()->unreadNotifications->count() }};

        // Function to check for new notifications
        function checkNotifications() {
            fetch('{{ route('notifications.unread-count') }}')
                .then(response => response.json())
                .then(data => {
                    const currentCount = data.count;
                    const badge = document.querySelector('.notification-badge');
                    const bellIcon = document.querySelector('.notification-bell');

                    // Update badge count
                    if (badge) {
                        badge.textContent = currentCount;
                        if (currentCount > 0) {
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }

                    // If count increased, show animation and play sound
                    if (currentCount > lastNotificationCount && lastNotificationCount >= 0) {
                        // Add pulse animation to bell
                        if (bellIcon) {
                            bellIcon.classList.add('animate-pulse');
                            setTimeout(() => {
                                bellIcon.classList.remove('animate-pulse');
                            }, 2000);
                        }

                        // Optional: Show browser notification
                        if ("Notification" in window && Notification.permission === "granted") {
                            new Notification("New Notification", {
                                body: "You have new notifications in the ticketing system",
                                icon: "{{ asset('favicon.ico') }}"
                            });
                        }
                    }

                    lastNotificationCount = currentCount;
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        // Check for notifications every 30 seconds
        setInterval(checkNotifications, 30000);

        // Request browser notification permission only on user interaction (avoids browser errors)
        document.addEventListener('click', function onFirstClick(e) {
            const bell = e.target.closest('.notification-bell');
            if (!bell) return;
            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }
            // Remove listener after first attempt
            document.removeEventListener('click', onFirstClick);
        });
    </script>
    @livewireScripts
    @stack('scripts')
</body>

</html>
