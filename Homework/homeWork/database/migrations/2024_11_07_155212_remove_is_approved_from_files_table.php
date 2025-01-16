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
        // في هذا الجزء، لن نحتاج لإضافة أي شيء، لأننا فقط نريد حذف العمود.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف العمود 'is_approved' من جدول 'files'
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};

