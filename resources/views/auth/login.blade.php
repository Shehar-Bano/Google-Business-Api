@extends('layouts/blankLayout')

@section('title', 'Login - Squash Pro')

@section('page-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">
@endsection

@section('content')
    <div class="login-wrapper">
        <div class="login-left"></div>

        <div class="login-right">
            <div class="login-card">
                <div class="app-brand justify-content-center mb-4">
                    <a href="{{ url('/') }}" class="app-brand-link gap-2">
                        {{-- <span class="app-brand-logo demo">
                            @include('_partials.macros', ['height' => 20, 'color' => '#B5F23C'])
                        </span> --}}
                        <span class="app-brand-text demo text-heading fw-semibold">Squash Pro</span>
                    </a>
                </div>

                <h4 class="mb-2">Welcome to Squash Pro!</h4>
                <p class="mb-4">Please sign-in to your account and start managing your squash operations</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form class="mb-3" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-floating form-floating-outline mb-3">
                        <input type="text" class="form-control" id="email" name="email"
                            placeholder="Enter your email or username" autofocus required>
                        <label for="email">Email or Username</label>
                    </div>
                    <div class="mb-3">
                        <div class="form-password-toggle">
                            <div class="input-group input-group-merge">
                                <div class="form-floating form-floating-outline">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" required />
                                    <label for="password">Password</label>
                                </div>
                                <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me">
                            <label class="form-check-label" for="remember-me">
                                Remember Me
                            </label>
                        </div>
                        <a href="{{ route('password.request') }}" class="float-end mb-1">
                            <span>Forgot Password?</span>
                        </a>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary d-grid w-100">Login</button>
                    </div>
                </form>

                <p class="text-center">
                    <span>New on our platform?</span>
                    <a href="{{ route('register') }}">
                        <span>Create an account</span>
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
