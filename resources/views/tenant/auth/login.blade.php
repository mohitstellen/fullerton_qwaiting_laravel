@extends('layouts.app')

@section('content')
    @php
        $url = request()->url();
        $headerPage = App\Models\SiteDetail::FIELD_BUSINESS_LOGO;

        if (strpos($url, 'mobile/queue') !== false) {
            $headerPage = App\Models\SiteDetail::FIELD_MOBILE_LOGO;
        }

        $logo = App\Models\SiteDetail::viewImage($headerPage);
    @endphp

    <div class="relative p-0 bg-white z-1 dark:bg-gray-900 sm:p-0 h-screen h-sm pt-sm-40">
        <div class="relative flex flex-col lg:flex-row justify-center w-full h-screen h-sm dark:bg-gray-900 sm:p-0">
            <!-- Image Panel (Left Side) - Wider -->
            @include('tenant/auth/right-side')
            
            <!-- Form Panel (Right Side) - Smaller -->
            <div class="flex flex-col flex-1 w-full lg:w-2/5 bg-white">
                <div class="flex flex-col justify-center items-center min-h-full w-full max-w-md mx-auto px-8 py-12">
                    <!-- Blue Banner with Logo -->
                    <div class="mb-8 w-full" style="background-color: #1e40af; padding: 16px 20px; display: flex; align-items: center; justify-content: center;">
                        <!-- Fullerton Health Logo -->
                        <img src="{{ asset('images/FHC-Masterbrand-Logo-white1.png') }}" 
                             alt="Fullerton Health Logo" 
                             style="height: 50px; width: auto; display: block; object-fit: contain;" />
                    </div>

                    @if (session('status'))
                        <div style="color: green; margin-bottom: 15px;">
                            {{ session('status') }}
                        </div>
                    @endif

                    

                    @if (session('success'))
                        <div class="mb-4 p-3 text-sm text-green-800 bg-green-100 border border-green-300 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.loginstore') }}" class="w-full space-y-5">
                        @csrf
                        <!-- Username Input -->
                        <div>
                            <input type="text" 
                                   id="login" 
                                   name="login" 
                                   placeholder="Username"
                                   value="{{ old('login') }}"
                                   class="w-full h-11 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                   style="background-color: #fff; color: #333; border-radius: 4px;" />
                            @error('login')
                                <span class="text-sm text-red-500" style="color:red;font-weight:bold; display: block; margin-top: 5px;">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Password"
                                   class="w-full h-11 px-4 py-2.5 text-sm border border-gray-300 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                   style="background-color: #fff; color: #333; border-radius: 4px;" />
                            @error('password')
                                <span class="text-sm text-red-500" style="color:red;font-weight:bold; display: block; margin-top: 5px;">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- LOG IN Button -->
                        <div class="pt-1">
                            <button type="submit"
                                    class="w-full h-12 text-white font-semibold text-base transition-colors hover:opacity-90"
                                    style="background-color: #1e40af; letter-spacing: 2px; border-radius: 4px;">
                                LOG IN
                            </button>
                        </div>

                        <!-- Forgot Password Link -->
                        <div class="text-right pt-1">
                            <a href="{{ route('tenant.password.request') }}"
                               class="text-sm hover:underline"
                               style="color: #2563eb;">
                                Forgot Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection