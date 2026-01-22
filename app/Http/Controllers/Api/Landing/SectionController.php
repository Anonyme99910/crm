<?php

namespace App\Http\Controllers\Api\Landing;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        return response()->json(Section::orderBy('order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|string',
            'background_image' => 'nullable|string',
            'background_color' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ]);

        $section = Section::create($validated);
        return response()->json($section, 201);
    }

    public function show(Section $section)
    {
        return response()->json($section);
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'subtitle' => 'nullable|string',
            'content' => 'nullable|string',
            'background_image' => 'nullable|string',
            'background_video' => 'nullable|string',
            'background_color' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ]);

        $section->update($validated);
        return response()->json($section);
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm|max:51200',
            'section_id' => 'required|exists:sections,id',
        ]);

        $file = $request->file('video');
        $filename = 'hero_video_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('sections', $filename, 'public');

        return response()->json(['path' => $path]);
    }

    public function destroy(Section $section)
    {
        $section->delete();
        return response()->json(['message' => 'Section deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:sections,id',
            'sections.*.order' => 'required|integer',
        ]);

        foreach ($validated['sections'] as $sectionData) {
            Section::where('id', $sectionData['id'])->update(['order' => $sectionData['order']]);
        }

        return response()->json(['message' => 'Sections reordered successfully']);
    }
}
