<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->dateTime('date_time');
            $table->time('duration', 0);
            $table->enum('privacy', ['Private', 'Public'])->default('Private');
            $table->decimal('price');
            $table->integer('attendee_limit');
            $table->enum('focused_on_gender', ['Women', 'Men', 'Everyone']);
            $table->string('focused_on_age_range', 7);
            $table->enum('recurrence', ['No Repeat', 'Daily', 'Weekly', 'Monthly'])->default('No Repeat');
            $table->string('location');
            $table->float('longitude');
            $table->float('latitude');
            $table->text('description');
            $table->boolean('published')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('promote_event_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
