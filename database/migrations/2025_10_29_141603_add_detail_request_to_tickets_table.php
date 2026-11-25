<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing text data to valid JSON format
        DB::statement("UPDATE requests SET detail_request = NULL WHERE detail_request = ''");
        DB::statement("UPDATE requests SET detail_request = JSON_OBJECT('description', detail_request) WHERE detail_request IS NOT NULL AND detail_request != ''");

        Schema::table('requests', function (Blueprint $table) {
            // Change detail_request from text to json
            $table->json('detail_request')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Revert back to text
            $table->text('detail_request')->nullable()->change();
        });
    }
};
