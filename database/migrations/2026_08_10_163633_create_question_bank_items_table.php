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
        Schema::create('question_bank_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete(); // if you have topics
            $table->enum('question_type', ['mcq', 'true_false', 'fill_blank', 'short_answer', 'long_answer', 'matching']);
            $table->text('question_text');
            $table->json('options')->nullable();             // for MCQ/true_false/matching
            $table->json('correct_answer')->nullable();      // answer key for auto-grading
            $table->unsignedInteger('marks')->default(1);
            $table->unsignedInteger('negative_marks')->default(0);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->json('tags')->nullable();                // e.g. ["chapter-1", "algebra"]
            $table->text('explanation')->nullable();         // shown after attempt if configured
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['question_bank_id', 'question_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_bank_items');
    }
};
