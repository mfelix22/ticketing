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
        Schema::table('requests', function (Blueprint $table) {
            // Drop handling_user_id (redundant with acceptance_user_id)
            // Note: note and due_date fields already exist in the table
            if (Schema::hasColumn('requests', 'handling_user_id')) {
                $table->dropForeign(['handling_user_id']);
                $table->dropColumn('handling_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Restore handling_user_id
            $table->unsignedBigInteger('handling_user_id')->nullable()->after('note');
            $table->foreign('handling_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
