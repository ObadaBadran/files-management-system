<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsApprovedToFilesTable extends Migration
{
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            // إضافة عمود is_approved
            $table->boolean('is_approved')->default(false);  // القيمة الافتراضية هي false
        });
    }

    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            // حذف العمود في حالة التراجع عن المهاجر
            $table->dropColumn('is_approved');
        });
    }
}
