<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string('phone_number', 20)->nullable();
            $table->timestamp('birthday_at')->nullable();
            $table->enum('gender', ['Woman', 'Man', 'Other'])->nullable();
            $table->softDeletes();
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
            $table->dropColumn('phone_number');
            $table->dropColumn('birthday_at');
            $table->dropColumn('gender');
            $table->dropSoftDeletes();
        });
    }
}
