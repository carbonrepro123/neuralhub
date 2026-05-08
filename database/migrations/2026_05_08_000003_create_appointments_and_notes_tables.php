<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('appointment_type')->default('video');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('reason_for_visit')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('daily_room_url')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('video_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('daily');
            $table->string('external_room_id');
            $table->string('room_url');
            $table->text('doctor_token')->nullable();
            $table->text('patient_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('soap_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->longText('subjective')->nullable();
            $table->longText('objective')->nullable();
            $table->longText('assessment')->nullable();
            $table->longText('plan')->nullable();
            $table->string('review_status')->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->string('source')->default('manual');
            $table->timestamps();
        });

        Schema::create('patient_intake_forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->json('responses')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_intake_forms');
        Schema::dropIfExists('visit_notes');
        Schema::dropIfExists('soap_notes');
        Schema::dropIfExists('video_rooms');
        Schema::dropIfExists('appointments');
    }
};
