<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserGuide;
use App\Models\GuideCategory;
use Illuminate\Http\Request;

class UserGuideController extends Controller
{
    public function index()
    {
        $categories = GuideCategory::with(['guides' => function($query) {
            $query->where('is_published', true)->orderBy('order');
        }])->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show($slug)
    {
        $guide = UserGuide::where('slug', $slug)
                         ->where('is_published', true)
                         ->firstOrFail();

        $guide->increment('views');

        return response()->json([
            'success' => true,
            'data' => $guide
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $guides = UserGuide::where('is_published', true)
                          ->where('title', 'LIKE', "%{$query}%")
                          ->orWhere('content', 'LIKE', "%{$query}%")
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $guides
        ]);
    }
}
