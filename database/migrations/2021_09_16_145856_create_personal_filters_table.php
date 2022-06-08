<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_filters', function (Blueprint $table) {
            $table->id();
            $table->enum('interested', ['Women', 'Men', 'Everyone']);
            $table->string('age_range', 7);
            $table->integer('distance');
            $table->boolean('verified_profyle_only')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_filters');
    }
}
