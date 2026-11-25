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
        // Add requires_external_handling to request_types table
        Schema::table('request_types', function (Blueprint $table) {
            $table->boolean('requires_external_handling')->default(false)->after('description');
        });

        // Add handling_user_id to requests table
        Schema::table('requests', function (Blueprint $table) {
            $table->foreignId('handling_user_id')->nullable()->after('handling_user')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['handling_user_id']);
            $table->dropColumn('handling_user_id');
        });

        Schema::table('request_types', function (Blueprint $table) {
            $table->dropColumn('requires_external_handling');
        });
    }
};
