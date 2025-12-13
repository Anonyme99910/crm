<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->datetime('scheduled_at');
            $table->datetime('completed_at')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('notes')->nullable();
            $table->text('client_requirements')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('site_visit_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->enum('category', ['exterior', 'interior', 'measurements', 'existing_condition', 'other'])->default('other');
            $table->timestamps();
        });

        Schema::create('site_visit_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('room_name');
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_measurements')->nullable();
            $table->timestamps();
        });

        Schema::create('site_visit_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_visit_id')->constrained()->cascadeOnDelete();
            $table->string('report_number')->unique();
            $table->text('summary');
            $table->text('recommendations')->nullable();
            $table->json('scope_of_work')->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visit_reports');
        Schema::dropIfExists('site_visit_measurements');
        Schema::dropIfExists('site_visit_photos');
        Schema::dropIfExists('site_visits');
    }
};
