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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Khai báo khóa ngoại nếu cần (nếu bạn đã có các bảng tương ứng)
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
