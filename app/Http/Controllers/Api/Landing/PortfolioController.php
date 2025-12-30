<?php

namespace App\Http\Controllers\Api\Landing;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        return response()->json(
            PortfolioItem::with('media')
                ->orderBy('order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'client' => 'nullable|string',
            'project_date' => 'nullable|date',
            'location' => 'nullable|string',
            'details' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $portfolio = PortfolioItem::create($validated);
        return response()->json($portfolio->load('media'), 201);
    }

    public function show(PortfolioItem $portfolio)
    {
        return response()->json($portfolio->load('media'));
    }

    public function update(Request $request, PortfolioItem $portfolio)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'client' => 'nullable|string',
            'project_date' => 'nullable|date',
            'location' => 'nullable|string',
            'details' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $portfolio->update($validated);
        return response()->json($portfolio->load('media'));
    }

    public function destroy(PortfolioItem $portfolio)
    {
        $portfolio->media()->delete();
        $portfolio->delete();
        return response()->json(['message' => 'Portfolio item deleted successfully']);
    }
}
