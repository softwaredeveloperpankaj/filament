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
        Schema::create('exam_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_student_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->nullable()->constrained()->nullOnDelete();

            // Marks breakdown
            $table->unsignedInteger('theory_obtained')->default(0);
            $table->unsignedInteger('theory_maximum')->default(0);
            $table->unsignedInteger('practical_obtained')->default(0);
            $table->unsignedInteger('practical_maximum')->default(0);
            $table->unsignedInteger('internal_obtained')->default(0);
            $table->unsignedInteger('internal_maximum')->default(0);

            // Computed
            $table->unsignedInteger('total_obtained')->default(0);
            $table->unsignedInteger('total_maximum')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_passed')->default(false);

            // Grading (role-controlled)
            $table->string('grade')->nullable();             // A+, A, B, C, F etc.
            $table->text('grade_remarks')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();

            // Source tracking
            $table->enum('source', ['online_auto', 'online_manual', 'offline_manual'])->default('offline_manual');
            $table->json('sub_question_marks')->nullable();  // breakdown per question
            $table->boolean('is_locked')->default(false);    // prevent edits after publish
            $table->timestamps();

            $table->unique(['exam_student_entry_id', 'exam_subject_id']);
            $table->index(['exam_id', 'is_locked']);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_marks');
    }
};
