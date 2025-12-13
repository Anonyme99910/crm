<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // HR Module - Attendance
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave'])->default('present');
            $table->text('notes')->nullable();
            $table->decimal('latitude_in', 10, 8)->nullable();
            $table->decimal('longitude_in', 11, 8)->nullable();
            $table->decimal('latitude_out', 10, 8)->nullable();
            $table->decimal('longitude_out', 11, 8)->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });

        // Quality Control Module
        Schema::create('quality_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->json('items');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('checklist_id')->constrained('quality_checklists')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'passed', 'failed', 'conditional'])->default('pending');
            $table->json('results')->nullable();
            $table->text('notes')->nullable();
            $table->integer('score')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quality_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->nullable()->constrained('quality_inspections')->nullOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->json('photos')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Maintenance Module
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['new', 'assigned', 'in_progress', 'completed', 'closed', 'cancelled'])->default('new');
            $table->string('category')->nullable();
            $table->json('photos')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_warranty')->default(false);
            $table->decimal('cost', 15, 2)->default(0);
            $table->timestamps();
        });

        // Marketing Dashboard
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('channel', ['facebook', 'google', 'instagram', 'tiktok', 'other']);
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('leads_generated')->default(0);
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('lead_sources_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('source');
            $table->integer('leads_count')->default(0);
            $table->integer('converted_count')->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['date', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_sources_stats');
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('quality_issues');
        Schema::dropIfExists('quality_inspections');
        Schema::dropIfExists('quality_checklists');
        Schema::dropIfExists('attendance');
    }
};
