@extends('frontend.layouts.app')
@section('title', 'My Profile - YHBS')
@section('content')
    <!-- Breadcrumb section Start-->
    <div class="breadcrumb-section"
        style="background-image:linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url({{ asset('frontend/img/innerpages/breadcrumb-bg6.jpg') }});">
        <div class="container">
            <div class="banner-content">
                <h1>My Profile</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                    <li>Profile</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Profile Section Start -->
    <div class="customer-profile-section pt-100 pb-100">
        <div class="container">
            <div class="row">
                <!-- Profile Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="profile-sidebar">
                        <div class="profile-avatar-section text-center mb-4">
                            <div class="profile-avatar-large mb-3">
                                @if (auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Profile Avatar"
                                        class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <i class="bi bi-person-circle"></i>
                                @endif
                            </div>
                            <h5>{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: auth()->user()->name ?? 'Guest User' }}
                            </h5>
                            <p class="text-muted">Member since
                                {{ auth()->user()->created_at ? auth()->user()->created_at->format('M Y') : 'N/A' }}</p>
                        </div>

                        <div class="profile-menu">
                            <a href="{{ route('customer.dashboard') }}" class="menu-item">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a href="{{ route('customer.profile') }}" class="menu-item active">
                                <i class="bi bi-person me-2"></i>My Profile
                            </a>
                            <a href="{{ route('customer.bookings') }}" class="menu-item">
                                <i class="bi bi-calendar-check me-2"></i>My Bookings
                            </a>
                            <a href="{{ route('customer.logout') }}" class="menu-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="col-lg-9">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Personal Information -->
                    <div class="profile-card mb-4">
                        <div class="card-header">
                            <h4><i class="bi bi-person-lines-fill me-2"></i>Personal Information</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customer.profile.update') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Avatar Upload -->
                                <div class="mb-4">
                                    <label class="form-label">Profile Photo</label>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if (auth()->user()->avatar)
                                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                                    alt="Current Avatar" id="avatar-preview" class="rounded-circle"
                                                    style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                                <div id="avatar-preview"
                                                    class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                    style="width: 80px; height: 80px;">
                                                    <i class="bi bi-person-circle"
                                                        style="font-size: 60px; color: #ccc;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                                id="avatar" name="avatar"
                                                accept="image/jpeg,image/png,image/jpg,image/gif"
                                                onchange="previewAvatar(event)">
                                            <small class="text-muted d-block mt-1">JPG, PNG, GIF (Max: 2MB)</small>
                                            @error('avatar')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                            id="first_name" name="first_name"
                                            value="{{ old('first_name', auth()->user()->first_name ?? '') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                            id="last_name" name="last_name"
                                            value="{{ old('last_name', auth()->user()->last_name ?? '') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email"
                                            value="{{ old('email', auth()->user()->email ?? '') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone"
                                            value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                            placeholder="+1 234 567 8900">
                                        <small class="text-muted">Use numbers, spaces, +, -, (, )</small>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2 me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h4><i class="bi bi-shield-lock me-2"></i>Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('customer.password.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <div class="password-input-wrapper position-relative">
                                            <input type="password"
                                                class="form-control @error('current_password') is-invalid @enderror"
                                                id="current_password" name="current_password" required>
                                            <button type="button" class="password-toggle-btn"
                                                onclick="togglePassword('current_password')">
                                                <i class="bi bi-eye" id="current_password-icon"></i>
                                            </button>
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <div class="password-input-wrapper position-relative">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password" required>
                                            <button type="button" class="password-toggle-btn"
                                                onclick="togglePassword('password')">
                                                <i class="bi bi-eye" id="password-icon"></i>
                                            </button>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted">Min 8 characters</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm New
                                            Password</label>
                                        <div class="password-input-wrapper position-relative">
                                            <input type="password" class="form-control" id="password_confirmation"
                                                name="password_confirmation" required>
                                            <button type="button" class="password-toggle-btn"
                                                onclick="togglePassword('password_confirmation')">
                                                <i class="bi bi-eye" id="password_confirmation-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Preview avatar before upload
        function previewAvatar(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('avatar-preview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    // If preview is an img element
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Replace the div with an img element
                        const img = document.createElement('img');
                        img.id = 'avatar-preview';
                        img.src = e.target.result;
                        img.alt = 'Avatar Preview';
                        img.className = 'rounded-circle';
                        img.style.width = '80px';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';
                        preview.parentNode.replaceChild(img, preview);
                    }
                }

                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
