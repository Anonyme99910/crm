<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('channel', ['whatsapp', 'sms', 'email']);
            $table->string('subject')->nullable();
            $table->text('content');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('status', ['answered', 'missed', 'voicemail', 'busy', 'failed']);
            $table->integer('duration_seconds')->default(0);
            $table->text('notes')->nullable();
            $table->string('recording_path')->nullable();
            $table->timestamp('called_at');
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('to_email');
            $table->string('subject');
            $table->text('body');
            $table->enum('status', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed']);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number');
            $table->text('message');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed']);
            $table->string('external_id')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number');
            $table->text('message');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed']);
            $table->enum('message_type', ['text', 'image', 'document', 'audio', 'video', 'template']);
            $table->json('media')->nullable();
            $table->string('external_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('message_templates');
    }
};
