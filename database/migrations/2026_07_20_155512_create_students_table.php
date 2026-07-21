<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Relations — structural, not personal info
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_class_id');
            $table->foreignId('section_id');
            $table->foreignId('form_template_id');

            // Identity — the only fixed columns needed outside form_data
            $table->string('registration_number')->unique(); // auto-generated e.g. REG-2026-00001
            $table->string('roll_no')->nullable();           // assigned after admission confirm
            $table->date('admission_date')->nullable();         // admission date
            $table->string('academic_year');

            // All personal info lives here — from your form builder
            $table->json('form_data');

            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
