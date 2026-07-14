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
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            $table->foreignId('branch_class_id')->constrained('branch_classes')->cascadeOnDelete();

            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();

            // A section can only be assigned once to a class in a branch
            $table->unique(['branch_id', 'branch_class_id', 'section_id'], 'unique_class_section');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
