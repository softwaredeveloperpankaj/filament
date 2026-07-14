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
        Schema::create('section_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            $table->foreignId('branch_class_id')->constrained('branch_classes')->cascadeOnDelete();

            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();

            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

            $table->foreignId('teacher_profile_id')->nullable()->constrained('teacher_profiles')->nullOnDelete();

            // same subject can't be assigned twice to same section in same class
            $table->unique(
                ['branch_id', 'branch_class_id', 'section_id', 'subject_id'],
                'unique_section_subject'
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_subjects');
    }
};
