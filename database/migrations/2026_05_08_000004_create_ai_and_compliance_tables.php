<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_agents', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('agent_type')->unique();
            $table->string('status')->default('active');
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_agent_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('agent_type');
            $table->longText('input_text')->nullable();
            $table->json('input_json')->nullable();
            $table->string('status')->default('queued');
            $table->string('priority')->default('normal');
            $table->timestamps();
        });

        Schema::create('ai_outputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_id')->constrained('ai_agent_tasks')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('agent_type');
            $table->longText('input_text')->nullable();
            $table->json('output_json')->nullable();
            $table->string('status')->default('completed');
            $table->boolean('reviewed_by_doctor')->default(false);
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('doctor_review_status')->default('pending');
            $table->string('disclaimer')->default('Doctor review required.');
            $table->timestamps();
        });

        Schema::create('compliance_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('certification_type')->index();
            $table->string('issuing_authority')->nullable();
            $table->string('license_number')->nullable();
            $table->string('state', 20)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('renewal_frequency')->nullable();
            $table->string('document_upload')->nullable();
            $table->string('status')->default('pending_review');
            $table->json('reminder_dates')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('compliance_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('compliance_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('disk')->default('s3');
            $table->string('status')->default('uploaded');
            $table->timestamps();
        });

        Schema::create('compliance_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('compliance_item_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default('email');
            $table->timestamp('send_at');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('compliance_reminders');
        Schema::dropIfExists('compliance_documents');
        Schema::dropIfExists('compliance_items');
        Schema::dropIfExists('ai_outputs');
        Schema::dropIfExists('ai_agent_tasks');
        Schema::dropIfExists('ai_agents');
    }
};
