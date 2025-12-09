@extends('frontend.layouts.app')
@section('title', 'Forgot Password - YHBS')
@section('content')
    <!-- Breadcrumb section Start-->
    {{-- <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Reset Password</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li>Forgot Password</li>
                </ul>
            </div>
        </div>
    </div> --}}

    <!-- Forgot Password Section Start -->
    <div class="authentication-section pt-100 pb-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-8">
                    <div class="auth-card">
                        <div class="auth-card-header text-center mb-4">
                            <div class="icon-wrapper mb-3">
                                <i class="bi bi-key-fill"></i>
                            </div>
                            <h3>Forgot Password?</h3>
                            <p class="text-muted">No worries! Enter your email and we'll send you reset instructions</p>
                        </div>

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('customer.forgot-password.submit') }}" method="POST" class="auth-form">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Enter your registered email"
                                    value="{{ old('email') }}" required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-send me-2"></i>Send Reset Link
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Remember your password?
                                    <a href="{{ route('customer.login') }}" class="fw-bold text-decoration-none">
                                        Back to Login
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
