<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admains', function (Blueprint $table) {
            $table->unsignedBigInteger('grade')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('admains', function (Blueprint $table) {
            $table->unsignedBigInteger('grade')->nullable(false)->change();
        });
    }
};
