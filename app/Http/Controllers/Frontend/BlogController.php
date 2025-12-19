<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::published()->latest('date');

        // Apply search filter if search parameter exists
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('content', 'like', '%' . $searchTerm . '%')
                    ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }

        $blogs = $query->paginate(12);

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
