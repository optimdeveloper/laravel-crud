<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->enum('auth_type', ['Basic', 'Apple', 'Google', 'Facebook']);
            $table->boolean('invisible_mode')->default(false);
            $table->string('auth_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('auth_type');
            $table->dropColumn('invisible_mode');
            $table->dropColumn('auth_data');
        });
    }
}
