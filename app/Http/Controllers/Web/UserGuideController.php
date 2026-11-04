<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserGuide;
use App\Models\GuideCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserGuideController extends Controller
{
    public function index()
    {
        $categories = GuideCategory::with(['guides' => function($query) {
            $query->where('is_published', true)->orderBy('order');
        }])->orderBy('order')->get();

        $recentGuides = UserGuide::where('is_published', true)
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        return view('user-guide.index', compact('categories', 'recentGuides'));
    }

    public function show($slug)
    {
        $guide = UserGuide::where('slug', $slug)
                         ->where('is_published', true)
                         ->firstOrFail();

        // Increment views
        $guide->increment('views');

        $categoryGuides = UserGuide::where('category', $guide->category)
                                  ->where('id', '!=', $guide->id)
                                  ->where('is_published', true)
                                  ->orderBy('order')
                                  ->limit(10)
                                  ->get();

        return view('user-guide.show', compact('guide', 'categoryGuides'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $guides = UserGuide::where('is_published', true)
                          ->where(function($q) use ($query) {
                              $q->where('title', 'LIKE', "%{$query}%")
                                ->orWhere('content', 'LIKE', "%{$query}%");
                          })
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return view('user-guide.search', compact('guides', 'query'));
    }

    public function category($slug)
    {
        $category = GuideCategory::where('slug', $slug)->firstOrFail();

        $guides = UserGuide::where('category', $category->slug)
                          ->where('is_published', true)
                          ->orderBy('order')
                          ->paginate(20);

        return view('user-guide.category', compact('category', 'guides'));
    }

    public function feedback(Request $request, $id)
    {
        $request->validate([
            'helpful' => 'required|boolean',
            'comment' => 'nullable|string|max:500'
        ]);

        $guide = UserGuide::findOrFail($id);

        // Check if user already gave feedback
        $existing = $guide->feedback()->where('user_id', auth()->id())->first();

        if ($existing) {
            $existing->update([
                'helpful' => $request->helpful,
                'comment' => $request->comment
            ]);
        } else {
            $guide->feedback()->create([
                'user_id' => auth()->id(),
                'helpful' => $request->helpful,
                'comment' => $request->comment
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'helpful_percentage' => $guide->helpful_percentage
        ]);
    }
}
