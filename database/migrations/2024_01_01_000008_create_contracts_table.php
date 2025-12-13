<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending_signature', 'active', 'completed', 'terminated', 'expired'])->default('draft');
            $table->decimal('total_value', 15, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('terms_conditions')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('client_signed_at')->nullable();
            $table->string('client_signature')->nullable();
            $table->timestamp('company_signed_at')->nullable();
            $table->string('company_signature')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contract_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('milestone')->nullable();
            $table->enum('status', ['pending', 'invoiced', 'paid'])->default('pending');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('contract_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('amendment_number');
            $table->string('title');
            $table->text('description');
            $table->decimal('value_change', 15, 2)->default(0);
            $table->integer('time_extension_days')->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('contract_amendments');
        Schema::dropIfExists('contract_payment_terms');
        Schema::dropIfExists('contracts');
    }
};
