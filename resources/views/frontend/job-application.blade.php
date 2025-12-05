@extends('frontend.layouts.app')
@section('content')

<section style="padding: 60px 0; background: #f7faff;">
    <div class="container text-center">

        <h2 style="font-weight: 700; font-size: 32px;">Careers at IKARUS Marine</h2>

        <p style="max-width: 750px; margin: 15px auto; color: #666; font-size: 16px;">
            Build your future with IKARUS United Marine Services. We are always looking for passionate
            professionals to join our growing operations in marine services, hospitality, tourism, and
            offshore support. Submit your application and our HR team will reach out if you’re a match.
        </p>

    </div>
</section>
<section style="padding: 50px 0;">
    <div class="container">

        <div class="row text-center">

            <div class="col-md-4 mb-4">
                <div style="background:#eef4ff; padding:28px; border-radius:16px; box-shadow:0 3px 10px rgba(0,0,0,0.05);">
                    <img src="https://cdn-icons-png.flaticon.com/512/1828/1828884.png" width="45" class="mb-3">
                    <h4 style="font-size:20px; font-weight:600;">Professional Growth</h4>
                    <p style="color:#555; font-size:14px;">
                        Learn and grow with supportive leadership, training programs, and real marine experience.
                    </p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div style="background:#fff5e8; padding:28px; border-radius:16px; box-shadow:0 3px 10px rgba(0,0,0,0.05);">
                    <img src="https://cdn-icons-png.flaticon.com/512/3209/3209358.png" width="45" class="mb-3">
                    <h4 style="font-size:20px; font-weight:600;">Dynamic Environment</h4>
                    <p style="color:#555; font-size:14px;">
                        Work across marine transport, heritage villages, sea cruises, and tourism services.
                    </p>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div style="background:#e8fff0; padding:28px; border-radius:16px; box-shadow:0 3px 10px rgba(0,0,0,0.05);">
                    <img src="https://cdn-icons-png.flaticon.com/512/679/679720.png" width="45" class="mb-3">
                    <h4 style="font-size:20px; font-weight:600;">Competitive Benefits</h4>
                    <p style="color:#555; font-size:14px;">
                        Enjoy a stable job, salary growth, safe working standards, and long-term benefits.
                    </p>
                </div>
            </div>

        </div>

    </div>
</section>
<section style="padding: 60px 0; background:#f9fafb;">
    <div class="container" style="max-width: 750px;">

        <h3 class="text-center mb-4" style="font-weight:700; font-size:28px;">Submit Your Job Application</h3>
        <p class="text-center" style="color:#666; margin-bottom:35px;">
            Please fill in the details below and upload your resume. Our HR team will review and get back to you.
        </p>

        <form action="{{ route('job.submit') }}" method="POST" enctype="multipart/form-data"
            style="background:#fff; padding:35px; border-radius:16px; box-shadow:0 3px 12px rgba(0,0,0,0.08);">
            @csrf

            <div class="mb-3">
                <label class="form-label" style="font-weight:600;">Full Name *</label>
                <input type="text" name="name" class="form-control" required style="padding:12px; border-radius:10px;">
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight:600;">Email Address *</label>
                <input type="email" name="email" class="form-control" required style="padding:12px; border-radius:10px;">
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight:600;">Phone Number *</label>
                <input type="text" name="phone" class="form-control" required style="padding:12px; border-radius:10px;">
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight:600;">Position Applying For *</label>
                <input type="text" name="position" class="form-control" required style="padding:12px; border-radius:10px;">
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight:600;">Upload Resume *</label>
                <input type="file" name="resume" accept=".pdf,.doc,.docx" class="form-control" required
                    style="padding:10px; border-radius:10px;">
            </div>

            <div class="mb-3">
                <label class="form-label" style="font-weight:600;">Message (Optional)</label>
                <textarea name="message" rows="4" class="form-control" style="padding:12px; border-radius:10px;"></textarea>
            </div>

            <div class="text-center">
                <button class="btn btn-primary px-5 py-2" style="border-radius:10px; font-size:16px;">
                    Submit Application
                </button>
            </div>

        </form>

    </div>
</section>


    </div>
</section>



@endsection
