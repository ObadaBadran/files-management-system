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
        Schema::table('file_logs', function (Blueprint $table) {
            $table->enum('action', ['in-check', 'out-check', 'updated', 'add', 'delete'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_logs', function (Blueprint $table) {
            $table->enum('action', ['in-check', 'out-check', 'updated'])->change();
        });
    }
};
