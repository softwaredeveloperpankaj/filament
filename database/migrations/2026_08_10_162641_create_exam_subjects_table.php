
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_class_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedInteger('max_marks')->default(100);
            $table->unsignedInteger('pass_marks')->default(33);
            $table->unsignedInteger('theory_marks')->default(80);
            $table->unsignedInteger('practical_marks')->default(20);
            $table->unsignedInteger('duration_minutes')->default(180);

            $table->json('paper_structure')->nullable();
            $table->boolean('is_graded')->default(true);
            $table->unsignedInteger('display_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['exam_id', 'subject_id'],
                'exam_subj_exam_subject_unique'
            );

            $table->index(
                ['exam_id', 'branch_class_id', 'section_id', 'display_order'],
                'exam_subj_class_section_order_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};
