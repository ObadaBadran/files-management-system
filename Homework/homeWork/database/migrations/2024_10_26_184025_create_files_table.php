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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->string('name');
         
            $table->enum('status', ['free', 'reserved'])->default('free');
            $table->unsignedBigInteger('reserved_by')->nullable();
            $table->timestamps();
        
            // العلاقات
          //  $table->foreign('file_group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('reserved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
