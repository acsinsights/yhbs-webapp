@extends('frontend.layouts.app')
@section('content')
    <!-- Start Breadcrumb section -->
    <div class="breadcrumb-section"
        style="
        background-image: linear-gradient(
            rgba(0, 0, 0, 0.3),
            rgba(0, 0, 0, 0.3)
          ),
          url({{ asset('frontend/img/carrers/career-employment-job-work-concept.jpg') }});
        padding: 180px 0;
        background-size: cover;
        background-position: center;
      ">
        <div class="container">
            <div class="banner-content">
                <h1>Careers</h1>
                <ul class="breadcrumb-list">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li>Careers</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- End Breadcrumb section -->

    <!-- Why Join Us Section Start-->
    <div class="why-join-section pt-100 mb-100">
        <div class="container">
            <div class="row justify-content-center mb-60 wow animate fadeInDown" data-wow-delay="200ms"
                data-wow-duration="1500ms">
                <div class="col-lg-9">
                    <div class="section-title text-center">
                        <h2>Why Work With IKARUS?</h2>
                        <svg height="6" viewbox="0 0 872 6" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM867 3.5L872 5.88675V0.113249L867 2.5V3.5ZM4.5 3.5H867.5V2.5H4.5V3.5Z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 wow animate fadeInLeft" data-wow-delay="200ms" data-wow-duration="1500ms">
                    <div class="single-benefit-card">
                        <div class="icon">
                            <svg width="60" height="60" viewbox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M30 5C16.2 5 5 16.2 5 30C5 43.8 16.2 55 30 55C43.8 55 55 43.8 55 30C55 16.2 43.8 5 30 5ZM30 50C19 50 10 41 10 30C10 19 19 10 30 10C41 10 50 19 50 30C50 41 41 50 30 50Z"
                                    fill="currentColor" />
                                <path
                                    d="M30 15C21.7 15 15 21.7 15 30C15 38.3 21.7 45 30 45C38.3 45 45 38.3 45 30C45 21.7 38.3 15 30 15ZM35 33L28 29V20H32V27L37 30L35 33Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <h4>Career Growth</h4>
                        <p>We invest in our employees' professional development with training programs and clear advancement
                            paths in Kuwait's premier marine services company.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow animate fadeInUp" data-wow-delay="300ms" data-wow-duration="1500ms">
                    <div class="single-benefit-card">
                        <div class="icon">
                            <svg width="60" height="60" viewbox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M30 5C16.2 5 5 16.2 5 30C5 43.8 16.2 55 30 55C43.8 55 55 43.8 55 30C55 16.2 43.8 5 30 5ZM30 50C19 50 10 41 10 30C10 19 19 10 30 10C41 10 50 19 50 30C50 41 41 50 30 50Z"
                                    fill="currentColor" />
                                <path
                                    d="M38 22H22C19.8 22 18 23.8 18 26V34C18 36.2 19.8 38 22 38H38C40.2 38 42 36.2 42 34V26C42 23.8 40.2 22 38 22ZM38 34H22V26H38V34Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <h4>Competitive Benefits</h4>
                        <p>Enjoy attractive salary packages, health insurance, annual bonuses, and additional perks that
                            reward your dedication and expertise.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow animate fadeInRight" data-wow-delay="400ms" data-wow-duration="1500ms">
                    <div class="single-benefit-card">
                        <div class="icon">
                            <svg width="60" height="60" viewbox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M45 15H38V10C38 8.3 36.7 7 35 7H25C23.3 7 22 8.3 22 10V15H15C13.3 15 12 16.3 12 18V48C12 49.7 13.3 51 15 51H45C46.7 51 48 49.7 48 48V18C48 16.3 46.7 15 45 15ZM26 11H34V15H26V11ZM44 47H16V19H22V23H38V19H44V47Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <h4>Dynamic Work Environment</h4>
                        <p>Work in an exciting, fast-paced environment with a diverse team across marine operations,
                            tourism,
                            and hospitality sectors.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow animate fadeInLeft" data-wow-delay="200ms" data-wow-duration="1500ms">
                    <div class="single-benefit-card">
                        <div class="icon">
                            <svg width="60" height="60" viewbox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M30 5C16.2 5 5 16.2 5 30C5 43.8 16.2 55 30 55C43.8 55 55 43.8 55 30C55 16.2 43.8 5 30 5ZM30 50C19 50 10 41 10 30C10 19 19 10 30 10C41 10 50 19 50 30C50 41 41 50 30 50Z"
                                    fill="currentColor" />
                                <path d="M30 15L35 25L45 27L37 35L39 45L30 40L21 45L23 35L15 27L25 25L30 15Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <h4>Industry Leadership</h4>
                        <p>Be part of a company operating 27 vessels and serving thousands of customers, making a real
                            impact in marine tourism across Kuwait.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow animate fadeInUp" data-wow-delay="300ms" data-wow-duration="1500ms">
                    <div class="single-benefit-card">
                        <div class="icon">
                            <svg width="60" height="60" viewbox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M48 18H43V13C43 11.3 41.7 10 40 10H20C18.3 10 17 11.3 17 13V18H12C10.3 18 9 19.3 9 21V47C9 48.7 10.3 50 12 50H48C49.7 50 51 48.7 51 47V21C51 19.3 49.7 18 48 18ZM21 14H39V18H21V14ZM47 46H13V22H47V46Z"
                                    fill="currentColor" />
                                <path
                                    d="M30 26C26.7 26 24 28.7 24 32C24 35.3 26.7 38 30 38C33.3 38 36 35.3 36 32C36 28.7 33.3 26 30 26Z"
                                    fill="currentColor" />
                            </svg>
                        </div>
                        <h4>Modern Equipment</h4>
                        <p>Work with state-of-the-art marine vessels, modern facilities, and the latest technology in
                            Kuwait's
                            marine tourism industry.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 wow animate fadeInRight" data-wow-delay="400ms" data-wow-duration="1500ms">
                    <div class="single-benefit-card">
                        <div class="icon">
                            <svg width="60" height="60" viewbox="0 0 60 60" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M30 5C16.2 5 5 16.2 5 30C5 43.8 16.2 55 30 55C43.8 55 55 43.8 55 30C55 16.2 43.8 5 30 5ZM30 50C19 50 10 41 10 30C10 19 19 10 30 10C41 10 50 19 50 30C50 41 41 50 30 50Z"
                                    fill="currentColor" />
                                <path d="M39 24L27 36L21 30L18 33L27 42L42 27L39 24Z" fill="currentColor" />
                            </svg>
                        </div>
                        <h4>Work-Life Balance</h4>
                        <p>We value your well-being with flexible schedules, annual leave, and a supportive culture that
                            respects personal time and family commitments.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Why Join Us Section End-->

    <!-- Application Form Section Start-->
    <div class="application-form-section mb-100" id="apply">
        <div class="container">
            <div class="row g-5 align-items-center">
                <!-- Left Side - Join Us Info -->
                <div class="col-lg-5">
                    <div class="join-info-card"
                        style="background: var(--primary-color2); padding: 50px 40px; border-radius: 20px; color: white; position: relative; overflow: hidden;">
                        <div
                            style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;">
                        </div>
                        <div
                            style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;">
                        </div>

                        <div style="position: relative; z-index: 2;">
                            <div
                                style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 15px; display: flex; align-items: center; justify-content: center; margin-bottom: 30px; backdrop-filter: blur(10px);">
                                <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M20 4C11.2 4 4 11.2 4 20C4 28.8 11.2 36 20 36C28.8 36 36 28.8 36 20C36 11.2 28.8 4 20 4ZM20 10C23.3 10 26 12.7 26 16C26 19.3 23.3 22 20 22C16.7 22 14 19.3 14 16C14 12.7 16.7 10 20 10ZM20 32C16 32 12.4 30 10.2 26.8C10.3 23.5 17 21.7 20 21.7C23 21.7 29.7 23.5 29.8 26.8C27.6 30 24 32 20 32Z"
                                        fill="white" />
                                </svg>
                            </div>

                            <h2
                                style="color: white; font-size: 32px; font-weight: 800; margin-bottom: 20px; line-height: 1.3;">
                                Join Our<br>Team Today
                            </h2>

                            <p
                                style="color: rgba(255,255,255,0.9); font-size: 16px; line-height: 1.8; margin-bottom: 35px;">
                                Be part of Kuwait's leading marine tourism company. Fill out the form and take the first
                                step toward an exciting career with us.
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 20px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div
                                        style="width: 50px; height: 50px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 style="color: white; font-size: 16px; font-weight: 700; margin: 0 0 5px 0;">
                                            Quick Response</h5>
                                        <p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 0;">We review
                                            applications within 48 hours</p>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div
                                        style="width: 50px; height: 50px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 8V12L15 15M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 style="color: white; font-size: 16px; font-weight: 700; margin: 0 0 5px 0;">
                                            Easy Process</h5>
                                        <p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 0;">Simple
                                            application, no complex steps</p>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div
                                        style="width: 50px; height: 50px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M17 20H22V18C22 16.3431 20.6569 15 19 15C18.0444 15 17.1931 15.4468 16.6438 16.1429M17 20H7M17 20V18C17 17.3438 16.8736 16.717 16.6438 16.1429M7 20H2V18C2 16.3431 3.34315 15 5 15C5.95561 15 6.80686 15.4468 7.35625 16.1429M7 20V18C7 17.3438 7.12642 16.717 7.35625 16.1429M7.35625 16.1429C8.0935 14.301 9.89482 13 12 13C14.1052 13 15.9065 14.301 16.6438 16.1429M15 7C15 8.65685 13.6569 10 12 10C10.3431 10 9 8.65685 9 7C9 5.34315 10.3431 4 12 4C13.6569 4 15 5.34315 15 7ZM21 10C21 11.1046 20.1046 12 19 12C17.8954 12 17 11.1046 17 10C17 8.89543 17.8954 8 19 8C20.1046 8 21 8.89543 21 10ZM7 10C7 11.1046 6.10457 12 5 12C3.89543 12 3 11.1046 3 10C3 8.89543 3.89543 8 5 8C6.10457 8 7 8.89543 7 10Z"
                                                stroke="white" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 style="color: white; font-size: 16px; font-weight: 700; margin: 0 0 5px 0;">
                                            Join 200+ Team</h5>
                                        <p style="color: rgba(255,255,255,0.8); font-size: 14px; margin: 0;">Become part of
                                            our growing family</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Application Form -->
                <div class="col-lg-7">
                    <div class="application-form-wrap"
                        style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
                        <div style="margin-bottom: 30px;">
                            <h3 style="font-size: 28px; font-weight: 800; color: #1a1a1a; margin-bottom: 10px;">Apply Now
                            </h3>
                            <p style="color: #666; font-size: 15px; margin: 0;">Fill in your details and upload your resume
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success"
                                style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; gap: 0.75rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; flex-shrink: 0;"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span style="font-size: 14px; font-weight: 500;">{{ session('success') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('job-application') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-inner">
                                        <label for="name"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Full
                                            Name *</label>
                                        <input type="text" id="name" name="name" placeholder="John Doe"
                                            value="{{ old('name') }}" required
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        @error('name')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="email"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Email
                                            *</label>
                                        <input type="email" id="email" name="email"
                                            placeholder="john@example.com" value="{{ old('email') }}" required
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        @error('email')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="phone"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Phone
                                            *</label>
                                        <input type="tel" id="phone" name="phone" placeholder="+965 XXXX XXXX"
                                            value="{{ old('phone') }}" required
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        @error('phone')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="position"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Position
                                            *</label>
                                        <select id="position" name="position" required
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; background: white;">
                                            <option value="">Select Position</option>
                                            <option value="Marine Operations Manager"
                                                {{ old('position') == 'Marine Operations Manager' ? 'selected' : '' }}>
                                                Marine Operations Manager</option>
                                            <option value="Guest Experience Coordinator"
                                                {{ old('position') == 'Guest Experience Coordinator' ? 'selected' : '' }}>
                                                Guest Experience Coordinator</option>
                                            <option value="Marine Technician"
                                                {{ old('position') == 'Marine Technician' ? 'selected' : '' }}>Marine
                                                Technician</option>
                                            <option value="Digital Marketing Specialist"
                                                {{ old('position') == 'Digital Marketing Specialist' ? 'selected' : '' }}>
                                                Digital Marketing Specialist</option>
                                            <option value="Boat Captain"
                                                {{ old('position') == 'Boat Captain' ? 'selected' : '' }}>Boat Captain
                                            </option>
                                            <option value="Chef" {{ old('position') == 'Chef' ? 'selected' : '' }}>Chef
                                            </option>
                                            <option value="Receptionist"
                                                {{ old('position') == 'Receptionist' ? 'selected' : '' }}>Receptionist
                                            </option>
                                            <option value="Other" {{ old('position') == 'Other' ? 'selected' : '' }}>
                                                Other</option>
                                        </select>
                                        @error('position')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="experience"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Experience</label>
                                        <input type="text" id="experience" name="experience" placeholder="3 years"
                                            value="{{ old('experience') }}"
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        @error('experience')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-inner">
                                        <label for="resume"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Upload
                                            Resume (PDF) *</label>
                                        <input type="file" id="resume" name="resume" accept=".pdf" required
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease;">
                                        @error('resume')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-inner">
                                        <label for="cover_letter"
                                            style="font-size: 14px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; display: block;">Cover
                                            Letter</label>
                                        <textarea id="cover_letter" name="cover_letter" rows="3"
                                            placeholder="Tell us why you'd like to join IKARUS..."
                                            style="width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 10px; font-size: 14px; transition: all 0.3s ease; resize: vertical;">{{ old('cover_letter') }}</textarea>
                                        @error('cover_letter')
                                            <span class="text-danger" style="font-size: 13px;">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 25px;">
                                <button type="submit" class="primary-btn1 submit-application-btn"
                                    style="width: 100%; background: var(--primary-color2); color: white; padding: 14px 30px; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px;">
                                    Submit Application
                                    <svg width="18" height="10" viewbox="0 0 18 10"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M13.5 1L17 5M17 5L13.5 9M17 5H1" stroke="white" stroke-width="2"
                                            fill="none" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Application Form Section End-->

    <!-- Call to Action Section Start-->
    <div class="cta-section mb-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="cta-card text-center"
                        style="background: var(--primary-color2); padding: 60px 40px; border-radius: 15px; color: white;">
                        <h2 style="color: white; margin-bottom: 20px;">Have a Unique Skill Set?</h2>
                        <p style="color: rgba(255,255,255,0.9); font-size: 18px; margin-bottom: 30px;">
                            At IKARUS, we value exceptional talent across all fields. If you believe you can contribute
                            to Kuwait's leading marine tourism company with your unique expertise, we'd love to hear from
                            you.
                            Share your profile with us and let's explore how you can be part of our success story.
                        </p>
                        <a href="mailto:careers@ikarus.com" class="primary-btn1 cta-profile-btn"
                            style="background: white; color: var(--primary-color2); padding: 15px 40px; border-radius: 5px; text-decoration: none; display: inline-block; font-weight: 600;">
                            Send Your Profile
                            <svg width="18" height="10" viewbox="0 0 18 10" xmlns="http://www.w3.org/2000/svg"
                                style="margin-left: 10px; stroke: var(--primary-color2);">
                                <path d="M13.5 1L17 5M17 5L13.5 9M17 5H1" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Call to Action Section End-->

    <style>
        .single-benefit-card {
            background: #fff;
            padding: 35px 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .single-benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .single-benefit-card .icon {
            width: 70px;
            height: 70px;
            background: var(--primary-color2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: white;
        }

        .single-benefit-card h4 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .single-benefit-card p {
            font-size: 15px;
            line-height: 1.8;
            color: #666;
            margin: 0;
        }

        .job-position-card {
            background: #fff;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 2px solid transparent;
        }

        .job-position-card:hover {
            border-color: var(--primary-color2);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .job-header {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: flex-start;
        }

        .job-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: white;
        }

        .job-details h4 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .job-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .job-meta span {
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .job-meta i {
            color: var(--primary-color2);
            font-size: 16px;
        }

        .job-position-card>p {
            font-size: 15px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 20px;
        }

        .job-requirements h6 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1a1a1a;
        }

        .job-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .job-requirements ul li {
            font-size: 14px;
            color: #666;
            padding-left: 25px;
            position: relative;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .job-requirements ul li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--primary-color2);
            font-weight: 700;
            font-size: 16px;
        }

        .application-form-wrap {
            background: #fff;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .form-inner {
            margin-bottom: 0;
        }

        .form-inner label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .form-inner input,
        .form-inner select,
        .form-inner textarea {
            width: 100%;
            padding: 12px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-inner input:focus,
        .form-inner select:focus,
        .form-inner textarea:focus {
            border-color: var(--primary-color2);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-color2-opc), 0.1);
        }

        .submit-btn .primary-btn1 {
            background: var(--primary-color2);
            color: white;
            padding: 15px 45px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn .primary-btn1 svg {
            stroke: white;
            stroke-width: 2;
            fill: none;
        }

        @media (max-width: 991px) {
            .application-form-wrap {
                padding: 35px 25px;
            }

            .job-header {
                flex-direction: column;
                gap: 15px;
            }

            .single-benefit-card,
            .job-position-card {
                margin-bottom: 20px;
            }
        }
    </style>
@endsection
