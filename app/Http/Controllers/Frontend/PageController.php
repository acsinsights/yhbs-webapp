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

class PageController extends Controller
{
    /**
     * Display the home page.
     */
    public function home()
    {
        $houses = House::active()->take(3)->get();
        $rooms = Room::active()->take(3)->get();
        $boats = Boat::active()->take(3)->get();
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
