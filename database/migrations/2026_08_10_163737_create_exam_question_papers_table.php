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
        Schema::create('exam_question_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_bank_id')->nullable()->constrained()->nullOnDelete();
            $table->json('selected_questions')->nullable();  // array of question_bank_item_ids with assigned marks
            $table->unsignedInteger('total_marks')->default(0);
            $table->json('sections')->nullable();            // paper sections: { "A": ["mcq", 20], "B": ["short", 30] }
            $table->text('instructions')->nullable();
            $table->boolean('is_shuffled')->default(false);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'exam_subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_question_papers');
    }
};
