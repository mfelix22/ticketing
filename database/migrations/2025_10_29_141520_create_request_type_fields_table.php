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
        Schema::create('request_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_type_id')->constrained()->onDelete('cascade');
            $table->string('field_name'); // Label shown to user
            $table->string('field_key'); // Database key (e.g., 'address', 'phone')
            $table->string('field_type')->default('text'); // text, textarea, number, select, date, etc.
            $table->boolean('is_required')->default(false);
            $table->json('field_options')->nullable(); // For select/radio options
            $table->string('placeholder')->nullable();
            $table->text('validation_rules')->nullable(); // Laravel validation rules
            $table->integer('order')->default(0); // Display order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_type_fields');
    }
};
