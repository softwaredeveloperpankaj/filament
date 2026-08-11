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
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_subject_id')->constrained()->cascadeOnDelete();
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number')->nullable();
            $table->foreignId('invigilator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('instructions')->nullable();        // subject-specific instructions
            $table->timestamps();

            $table->index(['exam_id', 'exam_date']);            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
