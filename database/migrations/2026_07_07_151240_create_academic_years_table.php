<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AcademicYearStatus;
use App\Enums\AcademicTermType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // "2025-2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->enum('status', array_column(AcademicYearStatus::cases(), 'value'))
                ->default(AcademicYearStatus::UPCOMING->value);
            $table->enum('term_type', array_column(AcademicTermType::cases(), 'value'))
                ->default(AcademicTermType::ANNUAL->value);
            $table->json('settings')->nullable();       // grading scale, term dates, etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
