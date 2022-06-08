<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->text('about_me')->nullable();
            $table->string('lives_in')->nullable();
            $table->string('from')->nullable();
            $table->string('work')->nullable();
            $table->string('education')->nullable();
            $table->enum('status', ['Online', 'Offline', 'Other'])->default('Online');
            $table->timestamps();
            $table->softDeletes();

            $table->bigInteger('user_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}
