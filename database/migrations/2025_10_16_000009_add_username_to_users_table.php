<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First add the column without unique constraint
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->after('name')->nullable();
        });

        // Update existing users
        DB::table('users')->update([
            'username' => DB::raw('LOWER(REPLACE(name, " ", ""))')
        ]);

        // Now add unique constraint and make it required
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
