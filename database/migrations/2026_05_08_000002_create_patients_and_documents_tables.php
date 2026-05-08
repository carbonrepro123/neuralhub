<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('primary_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_member_id')->nullable();
            $table->json('medical_history')->nullable();
            $table->json('allergies')->nullable();
            $table->json('medications')->nullable();
            $table->json('conditions')->nullable();
            $table->longText('notes')->nullable();
            $table->string('consent_status')->default('pending');
            $table->timestamps();
        });

        Schema::create('patient_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('original_name');
            $table->string('disk')->default('s3');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('encryption_key_ref')->nullable();
            $table->string('status')->default('uploaded');
            $table->timestamps();
        });

        Schema::create('report_extractions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('patient_documents')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->longText('ocr_text')->nullable();
            $table->longText('summary')->nullable();
            $table->json('vitals_json')->nullable();
            $table->json('abnormal_findings_json')->nullable();
            $table->string('review_status')->default('pending_doctor_review');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('consent_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->string('consent_type');
            $table->string('status');
            $table->timestamp('recorded_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
        Schema::dropIfExists('report_extractions');
        Schema::dropIfExists('patient_documents');
        Schema::dropIfExists('patients');
    }
};
