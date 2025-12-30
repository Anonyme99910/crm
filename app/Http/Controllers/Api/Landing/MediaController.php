<?php

namespace App\Http\Controllers\Api\Landing;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,webm|max:51200',
            'mediable_type' => 'required|string',
            'mediable_id' => 'required|integer',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $file = $request->file('file');
        $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
        
        $path = $file->store('media/' . $type . 's', 'public');
        
        $thumbnail = null;
        if ($type === 'image') {
            $thumbnail = $path;
        }

        $media = Media::create([
            'mediable_type' => $validated['mediable_type'],
            'mediable_id' => $validated['mediable_id'],
            'type' => $type,
            'path' => $path,
            'thumbnail' => $thumbnail,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'order' => $validated['order'] ?? 0,
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ],
        ]);

        return response()->json($media, 201);
    }

    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $media->update($validated);
        return response()->json($media);
    }

    public function destroy(Media $media)
    {
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }
        
        $media->delete();
        return response()->json(['message' => 'Media deleted successfully']);
    }
}
