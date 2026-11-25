<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->boolean('requires_manager_approval')->default(false)->after('usage_hours');
            $table->foreignId('manager_approval_user_id')->nullable()->after('requires_manager_approval')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_approval_user_id');
            $table->dropColumn('requires_manager_approval');
        });
    }
};
