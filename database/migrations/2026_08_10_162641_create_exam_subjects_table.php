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
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('max_marks')->default(100);
            $table->unsignedInteger('pass_marks')->default(33);
            $table->unsignedInteger('theory_marks')->default(80);
            $table->unsignedInteger('practical_marks')->default(20);
            $table->unsignedInteger('duration_minutes')->default(180);
            $table->json('paper_structure')->nullable();     // e.g. { "mcq": 20, "short": 30, "long": 50 }
            $table->boolean('is_graded')->default(true);     // some subjects may be pass/fail only
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['exam_id', 'subject_id']);
            $table->index(['exam_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};
