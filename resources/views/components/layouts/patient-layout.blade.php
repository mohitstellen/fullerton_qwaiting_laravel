<!DOCTYPE html>
<html lang="{{ session('app_locale', app()->getLocale()) }}"
    dir="{{ session('app_locale') === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Patient Portal' }}</title>
    <link rel="icon" href="{{ url('images/favicon.ico') }}" />

    <script src="{{ asset('js/cdn/tailwind3.js') }}"></script>
    <script src="{{ asset('js/cdn/tailwind4.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css?v='.time()) }}">

    @if(session('app_locale') === 'ar')
    <link rel="stylesheet" href="{{ asset('css/style-rtl.css?v='.time()) }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/cdn/all.min.css') }}"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>

    @stack('styles')
    @livewireStyles
</head>

<body class="bg-gray-50 dark:bg-gray-900" x-data="{ mobileMenuOpen: false }">
    <!-- Header Navigation -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <?php
                    $teamId = tenant('id');
                    $firstLocation = App\Models\Location::where('team_id', $teamId)
                        ->where('status', 1)
                        ->orderBy('id')
                        ->first();
                    $sidebarlocation = $firstLocation ? $firstLocation->id : null;
                    $settingsidebar = App\Models\SiteDetail::viewImage('business_logo', $teamId, $sidebarlocation);
                    ?>
                    <a href="{{ route('tenant.patient.dashboard') }}">
                        <img class="dark:hidden h-12 w-auto" src="{{ url($settingsidebar) }}" alt="Logo" style="max-height:50px;width:auto" />
                        <img class="hidden dark:block h-12 w-auto" src="{{ url($settingsidebar) }}" alt="Logo" style="max-height:50px;width:auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex space-x-1">
                    <a href="{{ route('tenant.patient.book-appointment') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('patient.book-appointment') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>Book an Appointment
                    </a>
                    <a href="{{ route('tenant.patient.appointments') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('patient.appointments') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>My Appointments
                    </a>
                    <a href="{{ route('tenant.patient.profile') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('patient.profile') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>Profile
                    </a>
                    <a href="{{ route('tenant.patient.dependents') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('patient.dependents') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>Dependents
                    </a>
                    <a href="{{ route('tenant.patient.change-password') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('patient.change-password') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>Change Password
                    </a>
                    @if(session('patient_member_id') && session('patient_customer_type') === 'Private')
                    <a href="{{ route('tenant.patient.cart') }}" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 {{ request()->routeIs('patient.cart') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>Cart
                        @php
                            $cartCount = count(session('patient_cart', []));
                        @endphp
                        @if($cartCount > 0)
                            <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @endif
                    <form method="POST" action="{{ route('tenant.patient.logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>Logout
                        </button>
                    </form>
                </nav>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-gray-200 dark:border-gray-700">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ route('tenant.patient.book-appointment') }}" 
                        class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>Book an Appointment
                    </a>
                    <a href="{{ route('tenant.patient.appointments') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>My Appointments
                    </a>
                    <a href="{{ route('tenant.patient.profile') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>Profile
                    </a>
                    <a href="{{ route('tenant.patient.dependents') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>Dependents
                    </a>
                    <a href="{{ route('tenant.patient.change-password') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>Change Password
                    </a>
                    @if(session('patient_member_id') && session('patient_customer_type') === 'Private')
                    <a href="{{ route('tenant.patient.cart') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>Cart
                        @php
                            $cartCount = count(session('patient_cart', []));
                        @endphp
                        @if($cartCount > 0)
                            <span class="ml-1 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @endif
                    <form method="POST" action="{{ route('tenant.patient.logout') }}" class="block">
                        @csrf
                        <button type="submit" 
                            class="block w-full text-left px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    @livewireScripts
    <script src="{{ asset('js/cdn/jquery.min.js') }}"></script>
    <script src="{{ asset('js/cdn/sweetalert2.js') }}"></script>
    <script src="{{ asset('/js/livewire.js?v='.time()) }}" type="module"></script>
    <script>
        // Initialize Alpine.js for mobile menu
        document.addEventListener('alpine:init', () => {
            Alpine.data('mobileMenu', () => ({
                mobileMenuOpen: false
            }));
        });
    </script>
    @stack('scripts')
</body>

</html>

