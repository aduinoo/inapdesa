@extends('layouts.app')

@section('content')

<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ================= LEFT IMAGE ================= -->
    <div class="relative hidden lg:flex lg:w-1/2 bg-cover bg-center"
        style="background-image: url('{{ asset('assets/images/homepage/login-hero.jpg') }}');">
        <div class="absolute inset-0 bg-black/30"></div>

        <div class="relative z-10 flex flex-col justify-center px-16 text-white">
            <h1 class="text-4xl font-bold leading-tight">
                Welcome Back to<br>
                <span class="text-green-300">RumaRehat</span>
            </h1>
        </div>
    </div>

    <!-- ================= RIGHT FORM ================= -->
    <div class="flex flex-1 items-center justify-center bg-[#f9f7ef] px-4 py-12">
        <div x-data="{ tab: '{{ $errors->any() ? 'signup' : 'login' }}' }"
             class="w-full max-w-md">

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <!-- ================= TABS ================= -->
            <div class="flex justify-center mb-8 border-b">
                <button @click="tab='login'"
                        :class="tab==='login' ? 'text-green-700 border-green-600' : 'text-gray-500'"
                        class="px-6 py-2 font-semibold border-b-2">
                    Log In
                </button>

                <button @click="tab='signup'"
                        :class="tab==='signup' ? 'text-green-700 border-green-600' : 'text-gray-500'"
                        class="px-6 py-2 font-semibold border-b-2">
                    Sign Up
                </button>
            </div>

            <!-- ================= LOGIN ================= -->
            <div x-show="tab==='login'" x-transition>
                <form method="POST" action="{{ route('login') }}"
                      onsubmit="return validateLoginForm(this)"
                      class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input name="email" type="email" value="{{ old('email') }}"
                               required autofocus autocomplete="username"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input name="password" type="password" required
                               autocomplete="current-password"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-600">
                            Remember me
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-green-700 hover:underline">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-green-600 py-3 text-white font-semibold hover:bg-green-700">
                        Log In
                    </button>

                    <p class="text-center text-sm text-gray-600">
                        Don’t have an account?
                        <button type="button" @click="tab='signup'"
                                class="text-green-700 font-semibold">
                            Sign Up
                        </button>
                    </p>
                </form>
            </div>

            <!-- ================= SIGN UP ================= -->
            <div x-show="tab==='signup'" x-transition>
                <form method="POST" action="{{ route('register') }}"
                      onsubmit="return validateSignupForm(this)"
                      class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input name="name" type="text" value="{{ old('name') }}"
                               required autocomplete="name"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input name="email" type="email" value="{{ old('email') }}"
                               required autocomplete="username"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input name="password" type="password" required
                               autocomplete="new-password"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input name="password_confirmation" type="password" required
                               autocomplete="new-password"
                               class="mt-1 w-full rounded-lg border-gray-300 focus:border-green-600 focus:ring-green-600">
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-green-600 py-3 text-white font-semibold hover:bg-green-700">
                        Sign Up
                    </button>

                    <p class="rounded-2xl bg-white/80 px-4 py-3 text-center text-sm text-gray-600 ring-1 ring-black/5">
                        New customer accounts will need email verification before using customer-only features.
                    </p>

                    <p class="text-center text-sm text-gray-600">
                        Already registered?
                        <button type="button" @click="tab='login'"
                                class="text-green-700 font-semibold">
                            Log In
                        </button>
                    </p>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function validateLoginForm(form) {
        if (!form.email.value || !form.password.value) {
            alert('Please fill in all login fields.');
            return false;
        }
        if (form.password.value.length < 8) {
            alert('Password must be at least 8 characters.');
            return false;
        }
        return true;
    }

    function validateSignupForm(form) {
        if (!form.name.value || !form.email.value || !form.password.value || !form.password_confirmation.value) {
            alert('Please fill in all signup fields.');
            return false;
        }
        if (form.password.value.length < 8) {
            alert('Password must be at least 8 characters.');
            return false;
        }
        if (form.password.value !== form.password_confirmation.value) {
            alert('Passwords do not match.');
            return false;
        }
        return true;
    }
</script>

@endsection
