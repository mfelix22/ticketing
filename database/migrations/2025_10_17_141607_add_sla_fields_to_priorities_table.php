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
        Schema::table('priorities', function (Blueprint $table) {
            // Add description for priority
            if (!Schema::hasColumn('priorities', 'description')) {
                $table->string('description')->nullable()->after('name');
            }

            // Replace single sla_hours with response and resolution times
            if (Schema::hasColumn('priorities', 'sla_hours')) {
                $table->dropColumn('sla_hours');
            }

            // Add separate SLA fields for response and resolution
            if (!Schema::hasColumn('priorities', 'sla_response_hours')) {
                $table->integer('sla_response_hours')->default(24)->after('description');
            }
            if (!Schema::hasColumn('priorities', 'sla_resolution_hours')) {
                $table->integer('sla_resolution_hours')->default(72)->after('sla_response_hours');
            }

            // Add visual indicators
            if (!Schema::hasColumn('priorities', 'color')) {
                $table->string('color', 20)->default('info')->after('sla_resolution_hours');
            }
            if (!Schema::hasColumn('priorities', 'icon')) {
                $table->string('icon', 50)->default('fa-circle')->after('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('priorities', function (Blueprint $table) {
            // Restore original sla_hours
            $table->integer('sla_hours')->nullable();

            // Drop new fields
            $table->dropColumn(['description', 'sla_response_hours', 'sla_resolution_hours', 'color', 'icon']);
        });
    }
};
