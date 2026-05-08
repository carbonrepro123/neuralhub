<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_feature_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('feature_name');
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'feature_key']);
        });

        Schema::create('doctor_agent_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_agent_id')->constrained('ai_agents')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'ai_agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_agent_assignments');
        Schema::dropIfExists('clinic_feature_flags');
    }
};
