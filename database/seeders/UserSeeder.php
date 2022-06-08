<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@myngly.com',
            'password' => Hash::make('123412'),
            'birthday_at' => Carbon::now(),
            'gender' => 'Man',
            'phone_number' => '+573013283038',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'receive_news' => 1,
            'show_gender' => 1,
            'auth_type' => 'Basic',
            'invisible_mode' => 0
        ]);
        DB::table('users')->insert([
            'name' => 'Temp',
            'email' => 'temp@myngly.com',
            'password' => Hash::make('123412'),
            'birthday_at' => Carbon::now(),
            'gender' => 'Man',
            'phone_number' => '+573013658974',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'receive_news' => 1,
            'show_gender' => 1,
            'auth_type' => 'Basic',
            'invisible_mode' => 0
        ]);
    }
}
