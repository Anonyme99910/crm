<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return response()->json(Testimonial::orderBy('order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string',
            'client_company' => 'nullable|string',
            'client_image' => 'nullable|string',
            'client_video' => 'nullable|string',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $testimonial = Testimonial::create($validated);
        return response()->json($testimonial, 201);
    }

    public function show(Testimonial $testimonial)
    {
        return response()->json($testimonial);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'client_name' => 'sometimes|string|max:255',
            'client_position' => 'nullable|string',
            'client_company' => 'nullable|string',
            'client_image' => 'nullable|string',
            'client_video' => 'nullable|string',
            'content' => 'sometimes|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $testimonial->update($validated);
        return response()->json($testimonial);
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return response()->json(['message' => 'Testimonial deleted successfully']);
    }
}
