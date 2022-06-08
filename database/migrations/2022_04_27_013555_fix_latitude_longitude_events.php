<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixLatitudeLongitudeEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('alter table events modify latitude DOUBLE(11,8)');
        DB::statement('alter table events modify longitude DOUBLE(11,8)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('alter table events modify latitude DOUBLE(8,2)');
        DB::statement('alter table events modify longitude DOUBLE(8,2)');
    }
}
