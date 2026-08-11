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
        Schema::create('online_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_student_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('ended_at')->nullable();       // auto-submit if time expires
            $table->enum('status', ['not_started', 'in_progress', 'submitted', 'auto_submitted', 'terminated'])->default('not_started');
            $table->json('answers')->nullable();             // { question_id: answer }
            $table->json('auto_score')->nullable();          // { question_id: marks_obtained }
            $table->unsignedInteger('total_obtained')->default(0);
            $table->unsignedInteger('total_maximum')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->json('time_spent_per_question')->nullable(); // analytics
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->unique(['exam_student_entry_id', 'exam_subject_id'], 'exam_attempts_student_subject_unique');
            $table->index(['exam_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_attempts');
    }
};
