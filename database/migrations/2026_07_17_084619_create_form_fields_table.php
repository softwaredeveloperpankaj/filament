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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_section_id')->nullable()->constrained('form_sections')->nullOnDelete();
            $table->string('field_key');
            $table->string('label');
            $table->string('type'); // text, email, number, date, textarea, select, radio, checkbox, file
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->string('option_layout')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('validation_rules')->nullable();
            $table->json('settings')->nullable(); // width, default, accept, etc.
            $table->json('visibility_conditions')->nullable(); // required_if / show_if style conditions
            $table->timestamps();

            $table->unique(['form_template_id', 'field_key']);
            $table->index(['form_template_id', 'form_section_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
