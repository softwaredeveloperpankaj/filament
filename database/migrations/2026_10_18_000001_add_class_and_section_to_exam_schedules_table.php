<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->foreignId('branch_class_id')->nullable()->after('exam_id')->constrained('branch_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->after('branch_class_id')->constrained('sections')->nullOnDelete();
        });

        // Backfill the current exam class/section for any already-created schedules.
        DB::table('exam_schedules')
            ->whereNotNull('exam_id')
            ->update([
                'branch_class_id' => DB::raw('(SELECT branch_class_id FROM exams WHERE exams.id = exam_schedules.exam_id)'),
                'section_id' => DB::raw('(SELECT section_id FROM exams WHERE exams.id = exam_schedules.exam_id)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_class_id');
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
