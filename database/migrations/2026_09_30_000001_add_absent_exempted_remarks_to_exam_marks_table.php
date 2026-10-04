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
        Schema::table('exam_marks', function (Blueprint $table) {
            $table->boolean('absent')->default(false)->after('is_locked');
            $table->boolean('exempted')->default(false)->after('absent');
            $table->text('remarks')->nullable()->after('exempted');
            
            $table->index(['exam_id', 'absent']);
            $table->index(['exam_id', 'exempted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_marks', function (Blueprint $table) {
            $table->dropIndex(['exam_id', 'absent']);
            $table->dropIndex(['exam_id', 'exempted']);
            $table->dropColumn(['absent', 'exempted', 'remarks']);
        });
    }
};