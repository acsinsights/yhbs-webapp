@extends('frontend.layouts.app')
@section('title', 'Verify Email - YHBS')
@section('content')
    <!-- Email Verification Section Start -->
    <div class="authentication-section pt-100 pb-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-8">
                    <div class="auth-card">
                        <div class="auth-card-header text-center mb-4">
                            <div class="icon-wrapper mb-3">
                                <i class="bi bi-envelope-check-fill"></i>
                            </div>
                            <h3>Verify Your Email</h3>
                            <p class="text-muted">Enter the 6-digit code sent to your email</p>
                            @if (session('email'))
                                <p class="text-muted small"><strong>{{ session('email') }}</strong></p>
                            @endif
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

                        <form action="{{ route('customer.verify-registration-otp.submit') }}" method="POST"
                            class="auth-form">
                            @csrf

                            <input type="hidden" name="email" value="{{ session('email') ?? old('email') }}">

                            <div class="form-group mb-3">
                                <label for="otp" class="form-label text-center d-block">
                                    Enter Verification Code
                                </label>
                                <input type="text"
                                    class="form-control text-center fw-bold fs-4 letter-spacing-3 @error('otp') is-invalid @enderror"
                                    id="otp" name="otp" placeholder="000000" maxlength="6"
                                    value="{{ old('otp') }}" required autofocus
                                    style="letter-spacing: 0.5rem; font-family: 'Courier New', monospace;">
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-center mt-2">
                                    <small><i class="bi bi-clock me-1"></i>Code expires in 10 minutes</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-circle me-2"></i>Verify Email
                            </button>

                            <div class="text-center">
                                <p class="mb-2">Didn't receive the code?</p>
                                <button type="button" id="resendBtn" class="btn btn-link fw-bold text-decoration-none p-0"
                                    data-resend-url="{{ route('customer.resend-registration-otp') }}" disabled>
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    <span id="resendText">Resend Code (<span id="countdown">60</span>s)</span>
                                </button>
                                <div id="resendMessage" class="mt-2"></div>
                            </div>

                            <hr class="my-3">

                            <div class="text-center">
                                <p class="mb-0">
                                    <a href="{{ route('customer.login') }}" class="text-muted text-decoration-none">
                                        <i class="bi bi-arrow-left me-1"></i>Back to Login
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
