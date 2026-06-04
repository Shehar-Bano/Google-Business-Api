@extends('layouts/blankLayout')

@section('title', 'Register - Squash Pro')

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
                        <span class="app-brand-logo demo">
                            @include('_partials.macros', ['height' => 20, 'color' => '#B5F23C'])
                        </span>
                        <span class="app-brand-text demo text-heading fw-semibold">Squash Pro</span>
                    </a>
                </div>

                <h4 class="mb-2">Create your account</h4>
                <p class="mb-4">Start managing your squash operations with a modern admin experience</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form class="mb-3" action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="form-floating form-floating-outline mb-3">
                        <input type="text" class="form-control" id="username" name="name" placeholder="Enter your username" autofocus required>
                        <label for="username">Username</label>
                    </div>
                    <div class="form-floating form-floating-outline mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="mb-3 form-password-toggle">
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

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" required>
                            <label class="form-check-label" for="terms-conditions">
                                I agree to <a href="javascript:void(0);">privacy policy & terms</a>
                            </label>
                        </div>
                    </div>

                    <button class="btn btn-primary d-grid w-100" type="submit">Sign up</button>
                </form>

                <p class="text-center">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}">
                        <span>Sign in instead</span>
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
