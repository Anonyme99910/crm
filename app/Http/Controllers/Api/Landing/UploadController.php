<?php

namespace App\Http\Controllers\Api\Landing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:51200',
            'type' => 'required|string',
        ]);

        $file = $request->file('file');
        $type = $validated['type'];
        
        // Determine folder based on type
        $folder = match($type) {
            'site_logo' => 'branding',
            'testimonial_image' => 'testimonials',
            'testimonial_video' => 'testimonials/videos',
            'portfolio_image' => 'portfolio',
            'portfolio_video' => 'portfolio/videos',
            default => 'uploads',
        };
        
        $path = $file->store($folder, 'public');
        
        return response()->json([
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
    
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string',
        ]);
        
        if (Storage::disk('public')->exists($validated['path'])) {
            Storage::disk('public')->delete($validated['path']);
            return response()->json(['message' => 'File deleted successfully']);
        }
        
        return response()->json(['message' => 'File not found'], 404);
    }
}
