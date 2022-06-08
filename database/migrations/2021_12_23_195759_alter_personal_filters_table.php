<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPersonalFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_filters', function(Blueprint $table) {
            $table->enum('mode', ['Networking', 'Love', 'Friendship']);
            $table->string('height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_filters', function(Blueprint $table) {
            $table->dropColumn('mode');
            $table->dropColumn('height');
        });
    }
}
