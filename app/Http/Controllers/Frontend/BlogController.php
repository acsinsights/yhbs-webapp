<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::published()
            ->latest('date')
            ->paginate(12);

        return view('frontend.blogs.index', compact('blogs'));
    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->published()
            ->with('tags')
            ->firstOrFail();

        // Get related blogs
        $relatedBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->latest('date')
            ->take(3)
            ->get();

        return view('frontend.blogs.show', compact('blog', 'relatedBlogs'));
    }
}
