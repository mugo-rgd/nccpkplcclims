<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGuide;
use App\Models\GuideCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserGuideController extends Controller
{
    public function index()
    {
        $guides = UserGuide::with('creator')->orderBy('created_at', 'desc')->paginate(20);
        return view('user-guide.index', compact('guides'));
    }

    public function create()
    {
        $categories = GuideCategory::all();
        return view('user-guide.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|exists:guide_categories,slug',
            'content' => 'required|string',
            'is_published' => 'boolean'
        ]);

        $guide = UserGuide::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . uniqid(),
            'category' => $validated['category'],
            'content' => $validated['content'],
            'content_html' => $validated['content'],
            'is_published' => $validated['is_published'] ?? true,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('user-guide.index')
                         ->with('success', 'Guide created successfully');
    }

    public function edit($id)
    {
        $guide = UserGuide::findOrFail($id);
        $categories = GuideCategory::all();
        return view('user-guide.edit', compact('guide', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|exists:guide_categories,slug',
            'content' => 'required|string',
            'is_published' => 'boolean'
        ]);

        $guide = UserGuide::findOrFail($id);
        $guide->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'content_html' => $validated['content'],
            'is_published' => $validated['is_published'] ?? true
        ]);

        return redirect()->route('user-guide.index')
                         ->with('success', 'Guide updated successfully');
    }

    public function destroy($id)
    {
        $guide = UserGuide::findOrFail($id);
        $guide->delete();

        return redirect()->route('user-guide.index')
                         ->with('success', 'Guide deleted successfully');
    }
}
