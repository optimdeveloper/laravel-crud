<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValidationCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validation_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->enum('type', ['Email', 'Sms', 'Other'])->default('Other');
            $table->dateTime('expiration');
            $table->boolean('used')->default(false);
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
        Schema::dropIfExists('validation_codes');
    }
}
