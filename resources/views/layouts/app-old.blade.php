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

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .main-sidebar {
            background: linear-gradient(180deg, #007bff 0%, #0056b3 100%);
        }

        .nav-sidebar .nav-link {
            color: rgba(255, 255, 255, .8);
        }

        .nav-sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, .1);
            color: #fff;
        }

        .nav-sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .content-wrapper {
            background-color: #f4f6f9;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown" x-data="{ open: false }">
                    <a class="nav-link" @click="open = !open" href="#" role="button">
                        <i class="fas fa-user"></i>
                        {{ Auth::user()->name }}
                        <i class="fas fa-caret-down ml-1"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" x-show="open" @click.away="open = false"
                        x-transition style="display: none;">
                        <a href="{{ route('profile') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" class="dropdown-item"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="brand-link">
                <img src="https://via.placeholder.com/33x33/007bff/ffffff?text=TS" alt="Ticketing System"
                    class="brand-image img-circle elevation-3">
                <span class="brand-text font-weight-light">Ticketing System</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="https://via.placeholder.com/40x40/28a745/ffffff?text={{ substr(Auth::user()->name, 0, 1) }}"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="{{ route('profile') }}" class="d-block text-white">{{ Auth::user()->name }}</a>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tickets.index') }}"
                                class="nav-link {{ request()->routeIs('tickets.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-ticket-alt"></i>
                                <p>My Tickets</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tickets.create') }}"
                                class="nav-link {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Create Ticket</p>
                            </a>
                        </li>
                        @if (Auth::user()->is_pic ?? false)
                            <li class="nav-item">
                                <a href="{{ route('tickets.assigned') }}"
                                    class="nav-link {{ request()->routeIs('tickets.assigned') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-check"></i>
                                    <p>Assigned to Me</p>
                                </a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}"
                                class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Reports</p>
                            </a>
                        </li>
                        @if (Auth::user()->is_pic ?? false)
                            <li class="nav-header">ADMINISTRATION</li>
                            <li class="nav-item">
                                <a href="{{ route('departments.index') }}"
                                    class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <p>Departments</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('request-types.index') }}"
                                    class="nav-link {{ request()->routeIs('request-types.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Request Types</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            @if (isset($header))
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                {{ $header }}
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                    @if (!request()->routeIs('dashboard'))
                                        <li class="breadcrumb-item active">
                                            {{ ucfirst(str_replace('.', ' ', Route::currentRouteName())) }}</li>
                                    @endif
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Ticketing System</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>

</html>
