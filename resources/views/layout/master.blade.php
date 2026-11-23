<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $title ?? 'Admin Dashboard | Vyora – The HR Guardian')</title>

    <meta name="description"
        content="Vyora Employee Portal – Manage your attendance, leaves, payroll, and profile with ease. Access payslips, apply for leave, and track performance in a secure and user-friendly HRM system.">

    <meta name="keywords"
        content="Employee dashboard, HRM employee panel, payroll management, leave tracking, attendance system, employee self service, payslip download, HR portal, profile management, Vyora HRMS, workforce management">

    <meta name="author" content="Vyora Team">
    <meta name="robots" content="index, follow">



    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/assets/img/apple-touch-icon.png') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Theme Script js -->
    <script src="assets/js/theme-script.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">

    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/icons/feather/feather.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/select2/css/select2.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Bootstrap Tagsinput CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">

    <!-- Summernote CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/summernote/summernote-lite.min.css') }}">

    <!-- Daterangepikcer CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/daterangepicker/daterangepicker.css') }}">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/%40simonwep/pickr/themes/nano.min.css') }}">

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.dataTables.css">

    {{-- Active sidebar menu highlight: the theme ships with these rules
         commented out in style.css, so a plain top-level item (Dashboard,
         etc.) never got a visible active background. Restored here using
         the theme's own navy/grey palette (#332c6d / #ebeaf0) so it matches
         the rest of the sidebar (Masters, submenu items) instead of the
         unrelated orange accent. --}}
    <style>
        .sidebar .sidebar-menu>ul>li.active>a {
            background: #ebeaf0;
            border-radius: 5px;
            color: #332c6d;
        }

        .sidebar .sidebar-menu>ul>li.active>a span {
            color: #332c6d;
        }

        .sidebar .sidebar-menu>ul>li.active>a i {
            color: #332c6d;
        }
        /* Sidebar look: small grey section captions, bold items, Masters highlighted when open */
        .sidebar .sidebar-menu .menu-title span {
            font-size: 11px; font-weight: 600; letter-spacing: .06em; color: #8a8fa3; text-transform: uppercase;
        }
        .sidebar .sidebar-menu>ul>li>a { font-weight: 600; color: #1f2430; border-radius: 8px; }
        .sidebar .sidebar-menu>ul>li>a i { color: #3a3f55; }
        /* top-level menus: smaller text + proper gap between icon and label */
        .sidebar .sidebar-menu>ul>li>a { font-size: 13.5px !important; padding-top: 9px; padding-bottom: 9px; }
        .sidebar .sidebar-menu>ul>li>a>span:not(.menu-arrow) { font-size: 13.5px !important; margin-left: 0 !important; }
        .sidebar .sidebar-menu>ul>li>a>i { font-size: 17px !important; margin-right: 10px !important; vertical-align: middle; }
        .sidebar .sidebar-menu>ul>li.submenu>a.subdrop { background: #ebeaf0; color: #1f2430; }
        /* submenu items: smaller text, active one = purple text + left bar */
        .sidebar .sidebar-menu li.submenu ul li a {
            font-weight: 400 !important; font-size: 12px !important; line-height: 1.3; color: #6b7080 !important;
            padding: 7px 12px 7px 16px !important; margin-left: -1px; background: transparent !important;
            border-left: 3px solid transparent; border-radius: 0;
        }
        .sidebar .sidebar-menu li.submenu ul li a:hover { color: #332c6d !important; }
        .sidebar .sidebar-menu li.submenu ul li a.active {
            color: #332c6d !important; font-weight: 500 !important; background: transparent !important;
            border-left-color: #332c6d;
        }
        .sidebar .sidebar-menu li.submenu>ul { border-left: 1px solid #e3e4ea; margin-left: 22px; padding-left: 0; }
    </style>

    {{-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

</head>

<body>
    {{-- <div id="global-loader">
		<div class="page-loader"></div>
	</div> --}}

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <div class="header">
            <div class="main-header">

                <div class="header-left">
                    <a href="#" class="logo">
                        <img src="{{ asset('frontend/assets/img/icons/logo.png') }}" alt="Logo">
                    </a>
                    <a href="#" class="dark-logo">
                        <img src="{{ asset('frontend/assets/img/icons/logo.png') }}" alt="Logo">
                    </a>
                </div>

                <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                    <span class="bar-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </a>

                <div class="header-user">
                    <div class="nav user-menu nav-list">

                        <div class="me-auto d-flex align-items-center" id="header-search">
                            <a id="toggle_btn" href="javascript:void(0);" class="btn btn-menubar me-1">
                                <i class="ti ti-arrow-bar-to-left"></i>
                            </a>
                            <!-- Search -->
                            <div class="input-group input-group-flat d-inline-flex me-1">
                                <span class="input-icon-addon">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search in HRMS">
                                <span class="input-group-text">
                                    <kbd>CTRL + / </kbd>
                                </span>
                            </div>
                            <!-- /Search -->
                            <div class="dropdown crm-dropdown">
                                <a href="#" class="btn btn-menubar me-1" data-bs-toggle="dropdown">
                                    <i class="ti ti-layout-grid"></i>
                                </a>
                                <div class="dropdown-menu dropdown-lg dropdown-menu-start">
                                    <div class="card mb-0 border-0 shadow-none">
                                        <div class="card-header">
                                            <h4>CRM</h4>
                                        </div>
                                        <div class="card-body pb-1">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <a href="#"
                                                        class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <i class="ti ti-user-shield text-default me-2"></i>Contacts
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                    <a href="#"
                                                        class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <i
                                                                class="ti ti-heart-handshake text-default me-2"></i>Deals
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                    <a href="#"
                                                        class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <i
                                                                class="ti ti-timeline-event-text text-default me-2"></i>Pipeline
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                </div>
                                                <div class="col-sm-6">
                                                    <a href="#"
                                                        class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <i class="ti ti-building text-default me-2"></i>Companies
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                    <a href="#"
                                                        class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <i class="ti ti-user-check text-default me-2"></i>Leads
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                    <a href="#"
                                                        class="d-flex align-items-center justify-content-between p-2 crm-link mb-3">
                                                        <span class="d-flex align-items-center me-3">
                                                            <i class="ti ti-activity text-default me-2"></i>Activities
                                                        </span>
                                                        <i class="ti ti-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="#" class="btn btn-menubar">
                                <i class="ti ti-settings-cog"></i>
                            </a>
                        </div>


                        <!-- /Horizontal Single -->

                        <div class="d-flex align-items-center">
                            <!-- <div class="me-1">
                                <a href="#" class="btn btn-menubar btnFullscreen">
                                    <i class="ti ti-maximize"></i>
                                </a>
                            </div> -->
                            <div class="dropdown me-1">
                                <a href="#" class="btn btn-menubar" data-bs-toggle="dropdown">
                                    <i class="ti ti-layout-grid-remove"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="card mb-0 border-0 shadow-none">
                                        <div class="card-header">
                                            <h4>Applications</h4>
                                        </div>
                                        <div class="card-body">
                                            <a href="#" class="d-block pb-2">
                                                <span class="avatar avatar-md bg-transparent-dark me-2"><i
                                                        class="ti ti-calendar text-gray-9"></i></span>Calendar
                                            </a>
                                            <a href="#" class="d-block py-2">
                                                <span class="avatar avatar-md bg-transparent-dark me-2"><i
                                                        class="ti ti-subtask text-gray-9"></i></span>To Do
                                            </a>
                                            <a href="#" class="d-block py-2">
                                                <span class="avatar avatar-md bg-transparent-dark me-2"><i
                                                        class="ti ti-notes text-gray-9"></i></span>Notes
                                            </a>
                                            <a href="#" class="d-block py-2">
                                                <span class="avatar avatar-md bg-transparent-dark me-2"><i
                                                        class="ti ti-folder text-gray-9"></i></span>File Manager
                                            </a>
                                            <a href="#" class="d-block py-2">
                                                <span class="avatar avatar-md bg-transparent-dark me-2"><i
                                                        class="ti ti-layout-kanban text-gray-9"></i></span>Kanban
                                            </a>
                                            <a href="#" class="d-block py-2 pb-0">
                                                <span class="avatar avatar-md bg-transparent-dark me-2"><i
                                                        class="ti ti-file-invoice text-gray-9"></i></span>Invoices
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="me-1 notification_item">
                                <a href="#" class="btn btn-menubar position-relative me-1"
                                    id="notification_popup" data-bs-toggle="dropdown">
                                    <i class="ti ti-bell"></i>
                                    <span class="notification-status-dot"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end notification-dropdown p-4">
                                    <div
                                        class="d-flex align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
                                        <h4 class="notification-title">Notifications (2)</h4>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="text-primary fs-15 me-3 lh-1">Mark all as
                                                read</a>
                                            <div class="dropdown">
                                                <a href="javascript:void(0);" class="bg-white dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                    <i class="ti ti-calendar-due me-1"></i>Today
                                                </a>
                                                <ul class="dropdown-menu mt-2 p-3">
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                            This Week
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                            Last Week
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                            Last Month
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="noti-content">
                                        <div class="d-flex flex-column">
                                            <div class="border-bottom mb-3 pb-3">
                                                <a href="#">
                                                    <div class="d-flex">
                                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                            <img src="{{ asset('frontend/assets/img/profiles/avatar-27.jpg') }}"
                                                                alt="Profile">
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-1"><span
                                                                    class="text-dark fw-semibold">Shawn</span>
                                                                performance in Math is below the threshold.</p>
                                                            <span>Just Now</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="border-bottom mb-3 pb-3">
                                                <a href="#" class="pb-0">
                                                    <div class="d-flex">
                                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                            <img src="{{ asset('frontend/assets/img/profiles/avatar-23.jpg') }}"
                                                                alt="Profile">
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-1"><span
                                                                    class="text-dark fw-semibold">Sylvia</span> added
                                                                appointment on 02:00 PM</p>
                                                            <span>10 mins ago</span>
                                                            <div
                                                                class="d-flex justify-content-start align-items-center mt-1">
                                                                <span class="btn btn-light btn-sm me-2">Deny</span>
                                                                <span class="btn btn-primary btn-sm">Approve</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="border-bottom mb-3 pb-3">
                                                <a href="#">
                                                    <div class="d-flex">
                                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                            <img src="{{ asset('frontend/assets/img/profiles/avatar-25.jpg') }}"
                                                                alt="Profile">
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-1">New student record <span
                                                                    class="text-dark fw-semibold"> George</span> is
                                                                created by <span
                                                                    class="text-dark fw-semibold">Teressa</span></p>
                                                            <span>2 hrs ago</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="border-0 mb-3 pb-0">
                                                <a href="#">
                                                    <div class="d-flex">
                                                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                            <img src="{{ asset('frontend/assets/img/profiles/avatar-01.jpg') }}"
                                                                alt="Profile">
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <p class="mb-1">A new teacher record for <span
                                                                    class="text-dark fw-semibold">Elisa</span> </p>
                                                            <span>09:45 AM</span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex p-0">
                                        <a href="#" class="btn btn-light w-100 me-2">Cancel</a>
                                        <a href="#" class="btn btn-primary w-100">View All</a>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="dropdown profile-dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                                    data-bs-toggle="dropdown">
                                    <span class="avatar avatar-sm online">
                                        <img src="{{ asset('frontend/assets/img/profiles/avatar-12.jpg') }}"
                                            alt="Img" class="img-fluid rounded-circle">
                                    </span>
                                </a>
                                <div class="dropdown-menu shadow-none">
                                    <div class="card mb-0">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-lg me-2 avatar-rounded">
                                                    <img src="{{ asset('frontend/assets/img/profiles/avatar-12.jpg') }}"
                                                        alt="img">
                                                </span>
                                                <div>

                                                    <h5 class="mb-0">{{ auth()->user()->name }}</h5>
                                                    <p class="fs-12 fw-medium mb-0"><a href="#">
                                                            <p class="fs-12 fw-medium mb-0">
                                                                {{ auth()->user()->email }}</p>
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <a class="dropdown-item d-inline-flex align-items-center p-0 py-2"
                                                href="#">
                                                <i class="ti ti-user-circle me-1"></i>My Profile
                                            </a>
                                            <a class="dropdown-item d-inline-flex align-items-center p-0 py-2"
                                                href="#">
                                                <i class="ti ti-settings me-1"></i>Settings
                                            </a>

                                            <a class="dropdown-item d-inline-flex align-items-center p-0 py-2"
                                                href="#">
                                                <i class="ti ti-circle-arrow-up me-1"></i>My Account
                                            </a>
                                            <a class="dropdown-item d-inline-flex align-items-center p-0 py-2"
                                                href="#">
                                                <i class="ti ti-question-mark me-1"></i>Knowledge Base
                                            </a>
                                        </div>
                                        <div class="card-footer">
                                            <form action="{{ route('logout') }}" method="POST" class="w-100">
                                                @csrf
                                                <button type="submit"
                                                    class="dropdown-item d-inline-flex align-items-center p-0 py-2 border-0 bg-transparent w-100 text-start">
                                                    <i class="ti ti-login me-2"></i>Logout
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

        </div>
        <!-- /Header -->

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <!-- Logo -->
            <div class="sidebar-logo">
                <a href="#" class="logo logo-normal">
                    <img src="{{ asset('frontend/assets/img/icons/logo.png') }}" alt="Logo">
                </a>
                <a href="#" class="logo-small">
                    <img src="{{ asset('frontend/assets/img/icons/logo-small.png') }}" alt="Logo">
                </a>
                <a href="#" class="dark-logo">
                    <img src="{{ asset('frontend/assets/img/icons/logo.png') }}" alt="Logo">
                </a>
            </div>
            <!-- /Logo -->
            <div class="modern-profile p-3 pb-0">
                <div class="text-center rounded bg-light p-3 mb-4 user-profile">
                    <div class="avatar avatar-lg online mb-3">
                        <img src="{{ asset('frontend/assets/img/profiles/avatar-02.jpg') }}" alt="Img"
                            class="img-fluid rounded-circle">
                    </div>
                    <h6 class="fs-12 fw-normal mb-1">Adrian Herman</h6>
                    <p class="fs-10">System Admin</p>
                </div>
                <div class="sidebar-nav mb-3">
                    <ul class="nav nav-tabs nav-tabs-solid nav-tabs-rounded nav-justified bg-transparent"
                        role="tablist">
                        <li class="nav-item"><a class="nav-link active border-0" href="#">Menu</a></li>
                        <li class="nav-item"><a class="nav-link border-0" href="#">Chats</a></li>
                        <li class="nav-item"><a class="nav-link border-0" href="#">Inbox</a></li>
                    </ul>
                </div>
            </div>
            <div class="sidebar-header p-3 pb-0 pt-2">
                <div class="text-center rounded bg-light p-2 mb-4 sidebar-profile d-flex align-items-center">
                    <div class="avatar avatar-md onlin">
                        <img src="{{ asset('frontend/assets/img/profiles/avatar-02.jpg') }}" alt="Img"
                            class="img-fluid rounded-circle">
                    </div>
                    <div class="text-start sidebar-profile-info ms-2">
                        <h6 class="fs-12 fw-normal mb-1">Adrian Herman</h6>
                        <p class="fs-10">System Admin</p>
                    </div>
                </div>
                <div class="input-group input-group-flat d-inline-flex mb-4">
                    <span class="input-icon-addon">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Search in HRMS">
                    <span class="input-group-text">
                        <kbd>CTRL + / </kbd>
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between menu-item mb-3">
                    <div class="me-3">
                        <a href="#" class="btn btn-menubar">
                            <i class="ti ti-layout-grid-remove"></i>
                        </a>
                    </div>
                    <div class="me-3">
                        <a href="#" class="btn btn-menubar position-relative">
                            <i class="ti ti-brand-hipchat"></i>
                            <span
                                class="badge bg-info rounded-pill d-flex align-items-center justify-content-center header-badge">5</span>
                        </a>
                    </div>
                    <div class="me-3 notification-item">
                        <a href="#" class="btn btn-menubar position-relative me-1">
                            <i class="ti ti-bell"></i>
                            <span class="notification-status-dot"></span>
                        </a>
                    </div>
                    <div class="me-0">
                        <a href="#" class="btn btn-menubar">
                            <i class="ti ti-message"></i>
                        </a>
                    </div>
                </div>
            </div>
            @php
                $authUser = Auth::user();
                $isSuperAdmin = $authUser->isSuperAdmin();
                // Super Admin = every menu, other roles = only the menus mapped in User Management > Menu Access
                $sidebarMenus = app(\App\Support\MenuRegistry::class)->treeFor($authUser);
                $lastSection = null;
            @endphp

            <div class="sidebar-inner slimscroll">
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>

                        {{-- MAIN MENU --}}
                        <li class="menu-title"><span>MAIN MENU</span></li>

                        @if ($isSuperAdmin)
                        <li>
                            <a href="{{ $isSuperAdmin ? route('dashboard') : route('home') }}"
                                class="{{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'active' : '' }}">
                                <i class="ti ti-home"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        @endif

                        {{-- ================= MENUS (from DB, defined in config/menus.php) ================= --}}
                        @foreach ($sidebarMenus as $topMenu)
                            @if (!empty($topMenu['section']) && $topMenu['section'] !== $lastSection)
                                <li class="menu-title"><span>{{ $topMenu['section'] }}</span></li>
                                @php $lastSection = $topMenu['section']; @endphp
                            @endif
                            @include('layout.partials.sidebar-menu', ['nodes' => [$topMenu], 'level' => 0])

                            {{-- Roles & Permissions sits right after Masters (Super Admin only) --}}
                            @if ($isSuperAdmin && ($topMenu['key'] ?? null) === 'masters')
                                <li>
                                    <a href="{{ (\Illuminate\Support\Facades\Route::has('access.index') ? route('access.index') : '#') }}" class="{{ request()->routeIs('access.*') ? 'active' : '' }}">
                                        <i class="ti ti-shield-lock"></i><span>Roles &amp; Permissions</span>
                                    </a>
                                </li>
                                @php $accessShown = true; @endphp
                            @endif
                        @endforeach

                        @if ($isSuperAdmin && empty($accessShown))
                            <li>
                                <a href="{{ (\Illuminate\Support\Facades\Route::has('access.index') ? route('access.index') : '#') }}" class="{{ request()->routeIs('access.*') ? 'active' : '' }}">
                                    <i class="ti ti-shield-lock"></i><span>Roles &amp; Permissions</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>

        </div>

        <!-- /Sidebar -->


        @yield('main-section')
    </div> <!-- Bootstrap Core JS -->
    <script data-cfasync="false" src="{{ asset('frontend/assets/email-decode.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Feather Icon JS -->
    <script src="{{ asset('frontend/assets/js/feather.min.js') }}"></script>

    <!-- Slimscroll JS -->
    <script src="{{ asset('frontend/assets/js/jquery.slimscroll.min.js') }}"></script>
    {{-- <script src="{{ asset('frontend/assets/js/jquery.dataTables.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('frontend/assets/js/dataTables.bootstrap5.min.js') }}"></script> --}}

    <!-- Chart JS -->
    <script src="{{ asset('frontend/assets/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/plugins/apexchart/chart-data.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('frontend/assets/plugins/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/plugins/chartjs/chart-data.js') }}"></script>

    <!-- Datetimepicker JS -->
    <script src="{{ asset('frontend/assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/bootstrap-datetimepicker.min.js') }}"></script>

    <!-- Daterangepikcer JS -->
    <script src="{{ asset('frontend/assets/plugins/daterangepicker/daterangepicker.js') }}"></script>

    <!-- Summernote JS -->
    <script src="{{ asset('frontend/assets/plugins/summernote/summernote-lite.min.js') }}"></script>

    <!-- Bootstrap Tagsinput JS -->
    <script src="{{ asset('frontend/assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>

    <!-- Select2 JS -->
    <script src="{{ asset('frontend/assets/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Color Picker JS -->
    <script src="{{ asset('frontend/assets/plugins/%40simonwep/pickr/pickr.es5.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/dataTables.bootstrap5.min.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('frontend/assets/js/todo.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/theme-colorpicker.js') }}"></script>
    <script src="{{ asset('frontend/assets/js/script.js') }}"></script>
    <script>
        // Highlight the active sidebar menu item based on the current URL.
        // NOTE: this must run AFTER script.js has finished its own sidebar
        // init (which only looks for an already-existing `.active` class),
        // otherwise the submenu never gets expanded/highlighted correctly.
        function markActiveSidebarMenu() {

            let currentPath = window.location.pathname.replace(/\/$/, "");
            let bestMatch = null;
            let bestLength = -1;

            // Find the single best-matching link (longest matching path),
            // so that a parent route like "/masters" doesn't steal the
            // highlight away from a more specific active child route.
            $('#sidebar-menu a[href]').each(function() {

                let link = $(this).attr('href');
                if (!link || link === '#' || link === 'javascript:void(0);') return;

                let linkPath;
                try {
                    linkPath = new URL(link, window.location.origin)
                        .pathname.replace(/\/$/, "");
                } catch (e) {
                    return;
                }

                if (linkPath === '' ) return;

                let matches = (currentPath === linkPath || currentPath.startsWith(linkPath + '/'));

                if (matches && linkPath.length > bestLength) {
                    bestMatch = this;
                    bestLength = linkPath.length;
                }
            });

            if (!bestMatch) return;

            // Clear any stale active/subdrop state first.
            $('#sidebar-menu li').removeClass('active');
            $('#sidebar-menu a').removeClass('active subdrop');
            $('#sidebar-menu ul ul').hide();

            let $link = $(bestMatch);

            $link.addClass('active');
            $link.closest('li').addClass('active');

            // Walk up every parent submenu (supports nested submenu-two)
            // and mark it active + open, matching the classes/behaviour
            // script.js uses (`subdrop`) so the arrow icon + styling stay
            // in sync with manual clicks.
            $link.parents('li.submenu, li.submenu-two').each(function() {
                let $li = $(this);
                let $parentLink = $li.children('a').first();

                $li.addClass('active');
                $parentLink.addClass('active subdrop');
                $li.children('ul').show();
            });
        }

        $(window).on('load', function() {
            markActiveSidebarMenu();
        });
    </script>



    {{-- Hide Add / Edit / Delete controls the logged-in role was not given (server also blocks the request) --}}
    @if (!empty($menuPerms) && !Auth::user()->isSuperAdmin())
        <script>
            (function () {
                var perms = @json($menuPerms);
                var rules = {
                    delete: '[data-bs-target="#delete_modal"],[data-bs-target*="delete" i],.btn-delete,.delete-btn,.delete-record,[data-action="delete"],[data-perm="delete"],a:has(> i.ti-trash),button:has(> i.ti-trash),a:has(> i.fa-trash),button:has(> i.fa-trash)',
                    edit: '[data-perm="edit"],.btn-edit,.edit-btn,a:has(> i.ti-edit),button:has(> i.ti-edit),a:has(> i.ti-pencil),button:has(> i.ti-pencil)',
                    add: '[data-perm="add"]'
                };
                function hide() {
                    Object.keys(rules).forEach(function (k) {
                        if (perms[k]) return;
                        var list = [];
                        try { list = document.querySelectorAll(rules[k]); } catch (e) {
                            try { list = document.querySelectorAll(rules[k].split(',').filter(function (x) { return x.indexOf(':has') < 0; }).join(',')); } catch (e2) {}
                        }
                        list.forEach(function (el) {
                            if (el.closest('.sidebar, .header, .modal')) return;
                            el.style.setProperty('display', 'none', 'important');
                        });
                    });
                }
                hide();
                document.addEventListener('DOMContentLoaded', hide);
                new MutationObserver(hide).observe(document.documentElement, {childList: true, subtree: true});
            })();
        </script>
    @endif
    @stack('after_scripts')
</body>


<!-- Mirrored from smartco.in/demo/html/template/index by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 08 Aug 2025 05:19:55 GMT -->

</html>
