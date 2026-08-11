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
        Schema::create('exam_student_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('roll_no')->index();              // snapshot at time of exam
            $table->enum('status', ['enrolled', 'admit_card_generated', 'appeared', 'absent', 'debarred', 'withdrawn'])->default('enrolled');
            $table->boolean('admit_card_printed')->default(false);
            $table->timestamp('admit_card_printed_at')->nullable();
            $table->json('admit_card_data')->nullable();     // cached data for reprint
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'section_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_student_entries');
    }
};
