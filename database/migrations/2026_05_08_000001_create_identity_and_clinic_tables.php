<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->index();
            $table->string('status')->default('active');
            $table->boolean('two_factor_enabled')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('clinics', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('npi_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 20)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('clinic_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['clinic_id', 'user_id']);
        });

        Schema::create('doctors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('specialty')->index();
            $table->string('license_number')->nullable();
            $table->string('state', 20)->nullable();
            $table->string('npi_number')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('clinic_users');
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('users');
    }
};
