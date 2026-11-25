<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('company')->after('email')->nullable();
            $table->foreignId('department_id')->after('company')->nullable()->constrained('departments');
            $table->string('intra_mail_address')->after('department_id')->nullable();
            $table->string('extra_mail_address')->after('intra_mail_address')->nullable();
            $table->enum('status', ['Requester', 'Approver', 'Both'])->after('extra_mail_address')->default('Requester');
            $table->boolean('is_pic')->after('status')->default(false);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'company',
                'department_id',
                'intra_mail_address',
                'extra_mail_address',
                'status',
                'is_pic'
            ]);
        });
    }
};
