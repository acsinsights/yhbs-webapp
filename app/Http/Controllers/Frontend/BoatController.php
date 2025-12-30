<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoatController extends Controller
{
    /**
     * Display a listing of boats
     */
    public function index(Request $request): View
    {
        $query = Boat::active()->orderBy('sort_order')->orderBy('name');

        // Filter by service type
        if ($request->has('service_type') && $request->service_type) {
            $query->where('service_type', $request->service_type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $boats = $query->paginate(12);

        return view('frontend.boats.index', compact('boats'));
    }

    /**
     * Display the specified boat
     */
    public function show(string $slug): View
    {
        $boat = Boat::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('frontend.boats.show', compact('boat'));
    }
}

