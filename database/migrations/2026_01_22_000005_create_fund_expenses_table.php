<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_fund_id')->constrained()->onDelete('cascade');
            $table->string('expense_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('category');
            $table->string('description');
            $table->date('expense_date');
            $table->string('receipt_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->string('vendor_name')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_expenses');
    }
};
