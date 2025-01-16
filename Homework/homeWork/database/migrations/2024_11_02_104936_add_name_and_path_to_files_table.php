<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
         
            if (!Schema::hasColumn('files', 'path')) {
                $table->string('path')->nullable(); // إضافة عمود path إذا لم يكن موجودًا
            }    
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn([ 'path']); // حذف الأعمدة عند التراجع
        });
    }
};
