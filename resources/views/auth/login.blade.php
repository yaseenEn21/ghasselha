<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <base href="../../../" />
    <title>{{ config('app.name', 'Laravel') }} - تسجيل الدخول</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Global Stylesheets Bundle -->
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .btn-submit {
            background-color: rgba(43, 55, 132, 1) !important;
        }
        .btn-submit:hover {
            background-color: rgba(43, 55, 132, 0.8) !important;
        }
    </style>

    <script>
        // Frame-busting - منع التحميل داخل iframe
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
    <!-- Theme mode setup -->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!-- خلفية الصفحة -->
        <style>
            body {
                background-image: url('{{ asset('assets/media/auth/bg10.jpeg') }}');
            }

            [data-bs-theme="dark"] body {
                background-image: url('{{ asset('assets/media/auth/bg10-dark.jpeg') }}');
            }
        </style>

        <!-- Authentication - Sign-in -->
        <div class="d-flex flex-column flex-lg-row flex-column-fluid align-items-center justify-content-center">
           
            <!-- Body (الفورم) -->
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
                <!-- Wrapper -->
                <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                    <!-- Content -->
                    <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                        <!-- Wrapper -->
                        <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">

                            {{-- إشعار نجاح/رسائل السيشن --}}
                            @if (session('status'))
                                <div class="alert alert-success w-100 text-center mb-5">
                                    {{ session('status') }}
                                </div>
                            @endif

                            {{-- أخطاء عامة --}}
                            @if ($errors->any())
                                {{-- لو حابب تعرض كل الأخطاء بشكل عام، فك التعليق التالي --}}
                                {{--
                                <div class="alert alert-danger w-100 mb-5">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                --}}
                            @endif

                            <!-- Form -->
                            <form class="form w-100" method="POST" action="{{ route('login') }}">
                                @csrf

                                <!-- Logo -->
                                <div class="mb-10 text-center">
                                    <img alt="Logo" src="{{ asset('assets/media/logos/logo.webp') }}" />
                                </div>

                                <!-- Heading -->
                                <div class="text-center mb-11">
                                    <div class="text-gray-500 fw-semibold fs-6">
                                        ادخل بياناتك للدخول إلى لوحة تحكم Ghasselha
                                    </div>
                                </div>

                                <!-- Separator -->
                                <div class="separator separator-content my-14">
                                    <span class="w-125px text-gray-500 fw-semibold fs-7">بإستخدام الإيميل</span>
                                </div>

                                <!-- Email -->
                                <div class="fv-row mb-8">
                                    <input type="email" placeholder="البريد الإلكتروني" name="email"
                                           autocomplete="username"
                                           value="{{ old('email') }}"
                                           class="form-control bg-transparent @error('email') is-invalid @enderror"
                                           required />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="fv-row mb-3">
                                    <input type="password" placeholder="كلمة المرور" name="password"
                                           autocomplete="current-password"
                                           class="form-control bg-transparent @error('password') is-invalid @enderror"
                                           required />
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Remember + Forgot -->
                                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                    {{-- <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="remember"
                                               id="remember_me" />
                                        <label class="form-check-label" for="remember_me">
                                            تذكرني
                                        </label>
                                    </div> --}}

                                    {{-- @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="link-primary">
                                            نسيت كلمة المرور؟
                                        </a>
                                    @endif --}}
                                </div>

                                <!-- Submit button -->
                                <div class="d-grid mb-10">
                                    <button type="submit" class="btn btn-submit text-white">
                                        <span class="indicator-label">تسجيل الدخول</span>
                                    </button>
                                </div>

                                <!-- Footer text -->
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    {{ date('Y') }}&copy;
                                    {{ config('app.name', 'Ghasselha') }} - جميع الحقوق محفوظة
                                </div>
                            </form>
                            <!-- End Form -->
                        </div>
                        <!-- End Wrapper -->

                        <!-- Footer (لغات + روابط) - اختياري -->
                        <div class="d-flex flex-stack d-none">
                            <div class="me-10">
                                <button class="btn btn-flex btn-link btn-color-gray-700 btn-active-color-primary rotate fs-base"
                                        data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start"
                                        data-kt-menu-offset="0px, 0px">
                                    <img data-kt-element="current-lang-flag" class="w-20px h-20px rounded ms-3"
                                         src="{{ asset('assets/media/flags/united-states.svg') }}" alt="" />
                                    <span data-kt-element="current-lang-name" class="ms-1">English</span>
                                    <i class="ki-duotone ki-down fs-5 text-muted rotate-180 m-0"></i>
                                </button>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-4 fs-7"
                                     data-kt-menu="true" id="kt_auth_lang_menu">
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link d-flex px-5" data-kt-lang="English">
                                            <span class="symbol symbol-20px ms-4">
                                                <img data-kt-element="lang-flag" class="rounded-1"
                                                     src="{{ asset('assets/media/flags/united-states.svg') }}" alt="" />
                                            </span>
                                            <span data-kt-element="lang-name">English</span>
                                        </a>
                                    </div>
                                    <!-- باقي اللغات لو حابب -->
                                </div>
                            </div>

                            <div class="d-flex fw-semibold text-primary fs-base gap-5">
                                <a href="#" target="_blank">الشروط</a>
                                <a href="#" target="_blank">الباقات</a>
                                <a href="#" target="_blank">تواصل معنا</a>
                            </div>
                        </div>
                        <!-- End Footer -->
                    </div>
                    <!-- End Content -->
                </div>
                <!-- End Wrapper -->
            </div>
            <!-- End Body -->
        </div>
        <!-- End Authentication - Sign-in -->
    </div>

    <script>
        var hostUrl = "{{ asset('assets') }}/";
    </script>

    <!-- Global Javascript Bundle -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    {{-- مهم: لا نستخدم general.js حتى لا يمنع إرسال الفورم الطبيعي للـ Laravel --}}
    {{-- <script src="{{ asset('assets/js/custom/authentication/sign-in/general.js') }}"></script> --}}
</body>
</html>