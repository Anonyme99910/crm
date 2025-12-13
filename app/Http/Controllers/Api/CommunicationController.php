<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\CallLog;
use App\Models\EmailLog;
use App\Models\SmsLog;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function templates(Request $request)
    {
        $templates = MessageTemplate::query()
            ->when($request->channel, fn($q, $channel) => $q->where('channel', $channel))
            ->where('is_active', true)
            ->get();

        return $this->successResponse($templates);
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:whatsapp,sms,email',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|array',
        ]);

        $template = MessageTemplate::create($request->all());

        return $this->successResponse($template, 'تم إنشاء القالب بنجاح', 201);
    }

    public function logCall(Request $request)
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'project_id' => 'nullable|exists:projects,id',
            'phone_number' => 'required|string|max:20',
            'direction' => 'required|in:inbound,outbound',
            'status' => 'required|in:answered,missed,voicemail,busy,failed',
            'duration_seconds' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'called_at' => 'required|date',
        ]);

        $log = CallLog::create([...$request->all(), 'user_id' => auth()->id()]);

        return $this->successResponse($log, 'تم تسجيل المكالمة بنجاح', 201);
    }

    public function callLogs(Request $request)
    {
        $logs = CallLog::with(['lead', 'user'])
            ->when($request->lead_id, fn($q, $id) => $q->where('lead_id', $id))
            ->latest('called_at')
            ->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($logs);
    }

    public function sendWhatsapp(Request $request)
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'phone_number' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $log = WhatsappLog::create([
            'lead_id' => $request->lead_id,
            'user_id' => auth()->id(),
            'phone_number' => $request->phone_number,
            'message' => $request->message,
            'direction' => 'outbound',
            'status' => 'pending',
            'message_type' => 'text',
        ]);

        return $this->successResponse($log, 'تم إرسال رسالة واتساب بنجاح', 201);
    }

    public function whatsappLogs(Request $request)
    {
        $logs = WhatsappLog::with(['lead', 'user'])
            ->when($request->lead_id, fn($q, $id) => $q->where('lead_id', $id))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return $this->paginatedResponse($logs);
    }

    public function sendSms(Request $request)
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'phone_number' => 'required|string|max:20',
            'message' => 'required|string|max:160',
        ]);

        $log = SmsLog::create([
            'lead_id' => $request->lead_id,
            'user_id' => auth()->id(),
            'phone_number' => $request->phone_number,
            'message' => $request->message,
            'direction' => 'outbound',
            'status' => 'pending',
        ]);

        return $this->successResponse($log, 'تم إرسال الرسالة النصية بنجاح', 201);
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'to_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $log = EmailLog::create([
            'lead_id' => $request->lead_id,
            'user_id' => auth()->id(),
            'to_email' => $request->to_email,
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $this->successResponse($log, 'تم إرسال البريد الإلكتروني بنجاح', 201);
    }
}
