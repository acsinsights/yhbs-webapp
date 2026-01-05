<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PolicyPage;
use Illuminate\Http\Request;

class PolicyPageController extends Controller
{
    public function show($slug)
    {
        $page = PolicyPage::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('frontend.policy-page', compact('page'));
    }
}
