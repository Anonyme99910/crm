<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function templates()
    {
        $templates = WhatsappTemplate::orderBy('category')->orderBy('name')->get();
        return response()->json($templates);
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:whatsapp_templates',
            'category' => 'required|string|max:50',
            'language' => 'required|string|size:2',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template = WhatsappTemplate::create($validated);
        return response()->json($template, 201);
    }

    public function updateTemplate(Request $request, WhatsappTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:whatsapp_templates,name,' . $template->id,
            'category' => 'sometimes|string|max:50',
            'content' => 'sometimes|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);
        return response()->json($template);
    }

    public function deleteTemplate(WhatsappTemplate $template)
    {
        $template->delete();
        return response()->json(['message' => 'Template deleted successfully']);
    }

    public function messages(Request $request)
    {
        $query = WhatsappMessage::with(['template', 'triggeredBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(50);
        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:whatsapp_templates,id',
            'phone_number' => 'required|string',
            'message' => 'required_without:template_id|string',
            'variables' => 'nullable|array',
            'recipient_type' => 'nullable|string',
            'recipient_id' => 'nullable|integer',
        ]);

        $message = $validated['message'] ?? null;

        if (isset($validated['template_id'])) {
            $template = WhatsappTemplate::find($validated['template_id']);
            $message = $this->parseTemplate($template->content, $validated['variables'] ?? []);
        }

        $whatsappMessage = WhatsappMessage::create([
            'template_id' => $validated['template_id'] ?? null,
            'recipient_type' => $validated['recipient_type'] ?? null,
            'recipient_id' => $validated['recipient_id'] ?? null,
            'phone_number' => $validated['phone_number'],
            'message' => $message,
            'variables' => $validated['variables'] ?? null,
            'direction' => 'outbound',
            'status' => 'pending',
            'triggered_by' => auth()->id(),
        ]);

        // Send via WhatsApp API
        $result = $this->sendViaWhatsappApi($whatsappMessage);

        if ($result['success']) {
            $whatsappMessage->update([
                'status' => 'sent',
                'whatsapp_message_id' => $result['message_id'] ?? null,
                'sent_at' => now(),
            ]);
        } else {
            $whatsappMessage->update([
                'status' => 'failed',
                'error_message' => $result['error'] ?? 'Unknown error',
            ]);
        }

        return response()->json($whatsappMessage->fresh(), 201);
    }

    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:whatsapp_templates,id',
            'recipients' => 'required|array|min:1',
            'recipients.*.phone_number' => 'required|string',
            'recipients.*.variables' => 'nullable|array',
            'recipients.*.recipient_type' => 'nullable|string',
            'recipients.*.recipient_id' => 'nullable|integer',
        ]);

        $template = WhatsappTemplate::find($validated['template_id']);
        $results = [];

        foreach ($validated['recipients'] as $recipient) {
            $message = $this->parseTemplate($template->content, $recipient['variables'] ?? []);

            $whatsappMessage = WhatsappMessage::create([
                'template_id' => $template->id,
                'recipient_type' => $recipient['recipient_type'] ?? null,
                'recipient_id' => $recipient['recipient_id'] ?? null,
                'phone_number' => $recipient['phone_number'],
                'message' => $message,
                'variables' => $recipient['variables'] ?? null,
                'direction' => 'outbound',
                'status' => 'pending',
                'triggered_by' => auth()->id(),
            ]);

            $results[] = $whatsappMessage;
        }

        // Queue for sending
        // In production, this would be dispatched to a job queue

        return response()->json([
            'message' => 'Messages queued for sending',
            'count' => count($results),
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();

        // Handle incoming webhook from WhatsApp
        if (isset($payload['statuses'])) {
            foreach ($payload['statuses'] as $status) {
                $message = WhatsappMessage::where('whatsapp_message_id', $status['id'])->first();
                if ($message) {
                    $newStatus = match($status['status']) {
                        'delivered' => 'delivered',
                        'read' => 'read',
                        'failed' => 'failed',
                        default => $message->status,
                    };

                    $message->update([
                        'status' => $newStatus,
                        'delivered_at' => $status['status'] === 'delivered' ? now() : $message->delivered_at,
                        'read_at' => $status['status'] === 'read' ? now() : $message->read_at,
                        'error_message' => $status['errors'][0]['message'] ?? $message->error_message,
                    ]);
                }
            }
        }

        // Handle incoming messages
        if (isset($payload['messages'])) {
            foreach ($payload['messages'] as $incomingMessage) {
                WhatsappMessage::create([
                    'phone_number' => $incomingMessage['from'],
                    'message' => $incomingMessage['text']['body'] ?? '',
                    'direction' => 'inbound',
                    'status' => 'delivered',
                    'whatsapp_message_id' => $incomingMessage['id'],
                    'delivered_at' => now(),
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function statistics()
    {
        $stats = [
            'total_sent' => WhatsappMessage::where('direction', 'outbound')->count(),
            'delivered' => WhatsappMessage::where('status', 'delivered')->count(),
            'read' => WhatsappMessage::where('status', 'read')->count(),
            'failed' => WhatsappMessage::where('status', 'failed')->count(),
            'pending' => WhatsappMessage::where('status', 'pending')->count(),
            'by_template' => WhatsappMessage::whereNotNull('template_id')
                ->selectRaw('template_id, COUNT(*) as count')
                ->groupBy('template_id')
                ->with('template:id,name')
                ->get(),
            'today' => WhatsappMessage::whereDate('created_at', today())->count(),
            'this_week' => WhatsappMessage::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'this_month' => WhatsappMessage::whereBetween('created_at', [now()->startOfMonth(), now()])->count(),
        ];

        return response()->json($stats);
    }

    private function parseTemplate($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    private function sendViaWhatsappApi(WhatsappMessage $message)
    {
        $apiKey = config('services.whatsapp.api_key');
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        if (!$apiKey || !$phoneNumberId) {
            // For development, simulate success
            return ['success' => true, 'message_id' => 'dev_' . uniqid()];
        }

        try {
            $response = Http::withToken($apiKey)
                ->post("https://graph.facebook.com/v17.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $message->phone_number,
                    'type' => 'text',
                    'text' => ['body' => $message->message],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('error.message', 'API Error'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
