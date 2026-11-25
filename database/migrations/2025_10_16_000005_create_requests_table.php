<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('request_type_id')->constrained('request_types');
            $table->foreignId('priority_id')->constrained('priorities');
            $table->foreignId('status_id')->constrained('request_statuses');
            $table->string('title');
            $table->text('detail_request');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('receipt_date')->nullable();
            $table->text('receipt_comment')->nullable();
            $table->boolean('is_continued')->default(false);
            $table->dateTime('finished_date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('handling_user_id')->nullable()->constrained('users');
            $table->dateTime('acceptance_date')->nullable();
            $table->foreignId('acceptance_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
};
