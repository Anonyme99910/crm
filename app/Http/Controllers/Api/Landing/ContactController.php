<?php

namespace App\Http\Controllers\Api\Landing;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $submissions = ContactSubmission::orderBy('created_at', 'desc')->get();
        return response()->json($submissions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $submission = ContactSubmission::create($validated);

        return response()->json([
            'message' => 'Thank you for contacting us! We will get back to you soon.',
            'submission' => $submission
        ], 201);
    }

    public function markAsRead($id)
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->update(['is_read' => true]);
        return response()->json($submission);
    }

    public function destroy($id)
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->delete();
        return response()->json(['message' => 'Contact submission deleted successfully']);
    }
}
