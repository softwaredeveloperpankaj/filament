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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_student_entry_id')->constrained()->cascadeOnDelete();

            // Aggregated results
            $table->unsignedInteger('grand_total_obtained')->default(0);
            $table->unsignedInteger('grand_total_maximum')->default(0);
            $table->decimal('overall_percentage', 5, 2)->default(0);
            $table->string('overall_grade')->nullable();
            $table->unsignedInteger('rank_in_class')->nullable();
            $table->unsignedInteger('rank_in_section')->nullable();
            $table->unsignedInteger('rank_in_branch')->nullable();

            // Pass/Fail
            $table->boolean('is_passed')->default(false);
            $table->json('failed_subjects')->nullable();     // subject_ids

            // Status
            $table->enum('status', ['draft', 'published', 'withheld', 'cancelled'])->default('draft');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->json('subject_wise_breakdown')->nullable(); // cached for report card

            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
