<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'recipient_type',
        'recipient_id',
        'phone_number',
        'message',
        'variables',
        'direction',
        'status',
        'whatsapp_message_id',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'triggered_by',
        'trigger_event',
    ];

    protected $casts = [
        'variables' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(WhatsappTemplate::class);
    }

    public function recipient()
    {
        return $this->morphTo();
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
