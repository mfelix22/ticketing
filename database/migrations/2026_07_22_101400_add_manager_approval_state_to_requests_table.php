<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->boolean('manager_approved')->default(false)->after('manager_approval_user_id');
            $table->foreignId('manager_approved_by')->nullable()->after('manager_approved')->constrained('users');
            $table->timestamp('manager_approved_at')->nullable()->after('manager_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('manager_approved_at');
            $table->dropConstrainedForeignId('manager_approved_by');
            $table->dropColumn('manager_approved');
        });
    }
};
