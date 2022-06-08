<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserToPassionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_to_passions', function (Blueprint $table) {
            $table->id();

            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('passion_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_passions');
    }
}
