@extends('frontend.layouts.app')
@section('content')


        <h3 class="text-center mb-4">Job Application Form</h3>

        <form action="{{ route('job.submit') }}" method="POST" enctype="multipart/form-data" style="max-width: 700px; margin: 0 auto;">
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- Phone -->
            <div class="mb-3">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <!-- Position -->
            <div class="mb-3">
                <label class="form-label">Position Applying For *</label>
                <input type="text" name="position" class="form-control" required>
            </div>

            <!-- Resume Upload -->
            <div class="mb-3">
                <label class="form-label">Upload Resume (PDF/DOC) *</label>
                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>

            <!-- Message -->
            <div class="mb-3">
                <label class="form-label">Message (Optional)</label>
                <textarea name="message" class="form-control" rows="4"></textarea>
            </div>

            <!-- Submit -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5 py-2">
                    Submit Application
                </button>
            </div>
        </form>

    </div>
</section>



@endsection
