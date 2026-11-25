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
            $table->boolean('requires_gm_approval')->default(false)->after('manager_approved_at');
            $table->foreignId('gm_approval_user_id')->nullable()->constrained('users')->after('requires_gm_approval');
            $table->boolean('gm_approved')->default(false)->after('gm_approval_user_id');
            $table->foreignId('gm_approved_by')->nullable()->constrained('users')->after('gm_approved');
            $table->timestamp('gm_approved_at')->nullable()->after('gm_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['requires_gm_approval', 'gm_approval_user_id', 'gm_approved', 'gm_approved_by', 'gm_approved_at']);
        });
    }
};
