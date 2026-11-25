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
            // Drop old handling_user_id (foreign key to users table)
            if (Schema::hasColumn('requests', 'handling_user_id')) {
                $table->dropForeign(['handling_user_id']);
                $table->dropColumn('handling_user_id');
            }

            // Add new completion fields
            $table->decimal('expenses_value', 15, 2)->nullable()->after('note');
            $table->decimal('usage_hours', 8, 2)->nullable()->after('expenses_value');
            $table->string('payment_flag')->nullable()->after('usage_hours');
            $table->string('handling_user')->nullable()->after('payment_flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Drop new fields
            $table->dropColumn(['expenses_value', 'usage_hours', 'payment_flag', 'handling_user']);

            // Restore old handling_user_id
            $table->foreignId('handling_user_id')->nullable()->after('note')->constrained('users');
        });
    }
};
