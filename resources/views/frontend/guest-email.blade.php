@extends('frontend.layouts.app')
@section('title', 'Enter Your Email - YHBS')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>Continue to Checkout</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li>Email Verification</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Guest Email Section Start -->
    <div class="py-5">
        @livewire('frontend.guest-email-modal')
    </div>
    <!-- Guest Email Section End -->
@endsection
