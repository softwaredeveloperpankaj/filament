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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // e.g. "Mid Term", "Final Term"
            $table->string('slug')->unique();                // e.g. "mid-term-2026"
            $table->enum('mode', ['online', 'offline'])->default('offline');
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();          // daily start time if recurring
            $table->time('end_time')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'ongoing', 'completed', 'results_published', 'cancelled'])->default('draft');
            $table->json('settings')->nullable();            // online settings: shuffle, negative_marking, show_result_immediately, available_from, available_until
            $table->text('instructions')->nullable();        // for admit card / paper
            $table->boolean('is_practical')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'branch_class_id', 'section_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
