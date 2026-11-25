<!DOCTYPE html>
<html lang="en">


<!-- Mirrored from smarthr.co.in/demo/html/template/login by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 08 Aug 2025 05:20:44 GMT -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Vyora - Smart HR Management System for employee management, payroll, attendance, and performance tracking.">
    <meta name="keywords"
        content="Vyora, HR management, employee management, payroll, attendance, leave tracking, performance evaluation, HR software">
    <meta name="author" content="Mahendra Sahu">
    <meta name="robots" content="index, follow">

    <!-- Title -->
    <title>Vyora – The HR Guardian
    </title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">


    <!-- Favicon -->

    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/assets/img/apple-touch-icon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/bootstrap.min.css') }}">

    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/icons/feather/feather.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/tabler-icons/tabler-icons.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/style.css') }}">
    @include('layout.focus-style')
</head>

<body class="bg-white">

    <div id="global-loader" style="display: none;">
        <div class="page-loader"></div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <div class="container-fuild">
            <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
                <div class="row">
                    <div class="col-lg-6">
                        <div
                            class="login-background login-side-panel position-relative d-lg-flex align-items-center justify-content-center d-none flex-wrap vh-100">
                            <style>
                                .login-background.login-side-panel { background: #8673aa; overflow: hidden; }
                                .login-background.login-side-panel::before { display: none; }
                                .login-side-panel { padding: 2.5rem; }
                                .login-side-panel .login-side-img { position: relative; z-index: 1; width: 100%; max-width: 100%; height: auto; max-height: calc(100vh - 5rem); object-fit: contain;
                                    -webkit-mask-image: linear-gradient(to bottom, transparent 0, #000 8%, #000 92%, transparent 100%), linear-gradient(to right, transparent 0, #000 8%, #000 92%, transparent 100%);
                                    -webkit-mask-composite: source-in;
                                            mask-image: linear-gradient(to bottom, transparent 0, #000 8%, #000 92%, transparent 100%), linear-gradient(to right, transparent 0, #000 8%, #000 92%, transparent 100%);
                                            mask-composite: intersect; }
                            </style>
                            <img src="{{ asset('frontend/assets/img/bg/login-side.png') }}" class="login-side-img" alt="Yora - Attendance, Leave &amp; Payroll">
                            </div>
                    </div>
                    <style>
                        .logo-text {
                            font-family: 'Inter', sans-serif;
                            /* Modern font */
                            font-size: 2.5rem;
                            /* Large heading */
                            font-weight: 700;
                            /* Bold */
                            color: #00B4EE;
                            /* Paytm blue */
                            letter-spacing: 2px;
                            /* Professional spacing */
                            /* text-transform: uppercase; */
                            /* Uppercase letters */
                            transition: color 0.3s ease;
                        }
                    </style>
                    <div class="col-lg-6 col-md-12 col-sm-12">
                        <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap">
                            <div class="col-md-7 mx-auto vh-100">
                                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
                                    @if (session('success'))
                                        <div class="toast align-items-center text-white bg-success border-0 show"
                                            role="alert">
                                            <div class="d-flex">
                                                <div class="toast-body">
                                                    {{ session('success') }}
                                                </div>
                                                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                                    data-bs-dismiss="toast"></button>
                                            </div>
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="toast align-items-center text-white bg-danger border-0 show"
                                            role="alert">
                                            <div class="d-flex">
                                                <div class="toast-body">
                                                    {{ session('error') }}
                                                </div>
                                                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                                    data-bs-dismiss="toast"></button>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <form action="{{ route('login') }}" method="POST" class="vh-100">
                                    @csrf
                                    <div class="vh-100 d-flex flex-column justify-content-between p-4 pb-0">
                                        <div class="logo-text mx-auto mt-4 mb-5 text-center">
                                            <img src="{{ asset('frontend/assets/img/icons/yora-logo-login.png') }}" alt="Yora" style="max-width:300px;width:100%;height:auto;">
                                        </div>


                                        <div class="">
                                            <div class="text-center mb-3">
                                                <h2 class="mb-2">Sign In</h2>
                                                <p class="mb-0">Please enter your details to access the dashboard</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <div class="input-group">
                                                    <input type="text" name="email"
                                                        value="{{ old('email', request()->cookie('remember_email')) }}"
                                                        class="form-control border-end-0">
                                                    <span class="input-group-text border-start-0">
                                                        <i class="ti ti-mail"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password</label>
                                                <div class="pass-group">
                                                    <input type="password" name="password"
                                                        value="{{ request()->cookie('remember_password') ? Crypt::decryptString(request()->cookie('remember_password')) : '' }}"
                                                        class="pass-input form-control">
                                                    <span class="ti toggle-password ti-eye-off"></span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check form-check-md mb-0">
                                                        <input class="form-check-input" id="remember_me"
                                                            name="remember_me" type="checkbox"
                                                            {{ request()->cookie('remember_email') ? 'checked' : '' }}>
                                                        <label for="remember_me"
                                                            class="form-check-label mt-0">Remember
                                                            Me</label>
                                                    </div>
                                                </div>
                                                {{-- <div class="text-end">
                                                    <a href="#" class="link-danger">Forgot Password?</a>
                                                </div> --}}
                                            </div>
                                            <div class="mb-3">
                                                <button type="submit" class="btn btn-primary w-100">Sign In</button>
                                            </div>

                                            @include('auth.partials.otp-login', ['otpContext'=>'superadmin','otpRoleSlug'=>null,'otpFieldName'=>'email'])
                                            {{-- <div class="text-center">
                                                <h6 class="fw-normal text-dark mb-0">Don’t have an account?
                                                    <a href="#" class="hover-a"> Create Account</a>
                                                </h6>
                                            </div> --}}


                                        </div>
                                        <div class="mt-5 pb-4 text-center">
                                            <p class="mb-0 text-gray-9">Copyright &copy; 2026 - Vyora – The HR
                                                Guardian
                                            </p>
                                        </div>
                                        <div class="mt-1 pb-2 text-center">
                                            <p class="mb-0 text-gray-9">
                                                Powered by <strong><a href="https://www.neuralinfo.in/"
                                                        target="_blank">Neural Info Solution</a></strong>
                                            </p>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Main Wrapper -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl, {
                    delay: 30000
                });
            });
            toastList.forEach(toast => toast.show());
        });
    </script>

    <!-- jQuery -->
    <script src="{{ asset('frontend/assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ asset('frontend/assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ asset('frontend/assets/js/feather.min.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('frontend/assets/js/script.js') }}"></script>

</body>


<!-- Mirrored from smarthr.co.in/demo/html/template/login by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 08 Aug 2025 05:20:44 GMT -->

</html>
