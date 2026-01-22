<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Room;
use App\Models\Boat;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Models\Statistic;
use App\Models\Blog;
use App\Models\PolicyPage;
use App\Models\CareerApplication;
use App\Mail\CareerApplicationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Display the home page.
     */
    public function home()
    {
        $houses = House::active()->take(5)->get();
        $rooms = Room::active()->take(5)->get();
        $boats = Boat::active()->take(5)->get();
        $sliders = Slider::active()->ordered()->get();
        $testimonials = Testimonial::active()->ordered()->take(6)->get();
        $statistics = Statistic::active()->ordered()->get();
        $blogs = Blog::published()->latest()->take(3)->get();
        $featuredBoats = Boat::active()->featured()->take(6)->get();

        return view('frontend.home', compact('houses', 'rooms', 'boats', 'sliders', 'testimonials', 'statistics', 'blogs', 'featuredBoats'));
    }

    /**
     * Display the about page.
     */
    public function about()
    {
        return view('frontend.about');
    }

    /**
     * Display the careers page.
     */
    public function careers()
    {
        return view('frontend.careers');
    }

    /**
     * Store a career application.
     */
    public function storeCareerApplication(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:255',
            'experience' => 'nullable|string|max:255',
            'resume' => 'required|file|mimes:pdf|max:5120', // 5MB max
            'cover_letter' => 'nullable|string|max:2000',
        ]);

        // Store the resume file
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('resumes', 'public');
            $validated['resume'] = $resumePath;
        }

        // Create the application
        $application = CareerApplication::create($validated);

        // Send email notification
        Mail::to(config('mail.from.address'))->send(new CareerApplicationMail($application));

        return redirect()->route('careers')->with('success', 'Your application has been submitted successfully! We will review it and get back to you soon.');
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return view('frontend.contact');
    }

    /**
     * Display the job application page.
     */
    public function jobApplication()
    {
        return view('frontend.job-application');
    }

    /**
     * Display a policy page by slug.
     */
    public function policyPage($slug)
    {
        $policyPage = PolicyPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('frontend.policy-page', compact('policyPage'));
    }
}