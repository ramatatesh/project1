<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admains', function (Blueprint $table) {
            // نضيف العمود الجديد
            $table->unsignedBigInteger('grade_id')->nullable()->after('user_id');

        });
    }

    public function down()
    {
        Schema::table('admains', function (Blueprint $table) {

            $table->dropColumn('grade_id');
        });
    }
};
