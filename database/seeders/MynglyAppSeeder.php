<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MynglyAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Users
        $gender = array('Woman', 'Man', 'Other');
        $bool = array(0, 1);
        $authType = array('Basic', 'Apple', 'Google', 'Facebook');
        for ($i = 0; $i < 5; $i++) {
            DB::table('users')->insert([
                'name' => Str::random(5),
                'email' => Str::random(5) . '@myngly.com',
                'receive_news' => $bool[array_rand($bool, 1)],
                'password' => Hash::make('123412'),
                'birthday_at' => Carbon::now(),
                'gender' => $gender[array_rand($gender, 1)],
                'show_gender' => $bool[array_rand($bool, 1)],
                'phone_number' => '+57' . rand(1000000000, 9999999999),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'receive_news' => $bool[array_rand($bool, 1)],
                'show_gender' => $bool[array_rand($bool, 1)],
                'auth_type' => $authType[array_rand($authType, 1)],
                'invisible_mode' => $bool[array_rand($bool, 1)]
            ]);
        }

        // validation code
        $type = array('Email', 'Sms', 'Other');
        $bool = array(0, 1);
        for ($i = 1; $i < 7; $i++) {
            DB::table('validation_codes')->insert([
                'code' => random_int(99999, 1000000),
                'type' => $type[array_rand($type, 1)],
                'expiration' => Carbon::tomorrow(),
                'used' => $bool[array_rand($bool, 1)],
                'user_id' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // User profile
        $status = array('Online', 'Offline', 'Other');
        for ($i = 1; $i < 7; $i++) {
            DB::table('user_profiles')->insert([
                'about_me' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Assumenda, possimus! Nulla eaque nesciunt dolorum doloribus expedita perferendis officia soluta aliquam voluptate, consequatur placeat tenetur error quae, omnis dignissimos animi velit!',
                'lives_in' => 'Colombia',
                'from' => 'Colombia',
                'work' => 'Work',
                'education' => 'University',
                'status' => $status[array_rand($status, 1)],
                'user_id' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Suscription
        $subscription = array('Free', 'Premium');
        $time = array(null, 1, 3, 5);
        for ($i = 1; $i < 7; $i++) {
            DB::table('subscriptions')->insert([
                'type' => $subscription[array_rand($subscription, 1)],
                'time' => $time[array_rand($time, 1)],
                'user_id' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // User photos x2
        $name = ['avatar-1.jpg','avatar-2.jpg','avatar-3.jpg','avatar-4.jpg','avatar-5.jpg','avatar-6.jpg', 'avatar-7.jpg', 'avatar-8.jpg'];
        for ($i = 1; $i < 7; $i++) {
            for ($j = 0; $j < 2; $j++) {
                DB::table('user_photos')->insert([
                    'name' => $name[$i],
                    'path' => $name[$i],
                    'user_id' => $i,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // Conected app
        $app = array('Facebook', 'Instagram', 'Whastapp', 'Other');
        for ($i = 1; $i < 7; $i++) {
            for ($j = 0; $j < 2; $j++) {
                DB::table('conected_apps')->insert([
                    'name' => $app[array_rand($app, 1)],
                    'user_id' => $i,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'app_data' => Str::random(10)
                ]);
            }
        }

        //Personal filters
        $mode = array('Networking', 'Love', 'Friendship');
        $interested = array('Women', 'Men', 'Everyone');
        $bool = array(0, 1);
        for ($i = 1; $i < 7; $i++) {
            DB::table('personal_filters')->insert([
                'interested' => $interested[array_rand($interested, 1)],
                'age_range' => '20-40',
                'distance' => random_int(99, 1000),
                'verified_profyle_only' => $bool[array_rand($bool, 1)],
                'user_id' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'mode' => $mode[array_rand($mode, 1)]
            ]);
        }

        // passions
        $passions = array('Parties', 'Movies', 'Cooking', 'Motorcycles', 'Podcast', 'Netflix', 'Swimming', 'Yoga',
                        'Drink & After work', 'Astrology', 'Voguing', 'Vegetarian', 'Reading', 'Road trips', 'Dancing',
                        'Tango', 'Gamer', 'Festivals', 'Art', 'Tea', 'Spirituality', 'Wine', 'Sports',
                        'Walking', 'Travel', 'DIY', 'Language Exchange', 'Writer', 'Karaoke', 'Environmentalism',
                        'Climbing', 'Hiking', 'Athlete', 'Fishing', 'Grab a drink', 'Dog lover',
                        'Brunch', 'No pressure meetups', 'Gardening');
        for ($i = 0; $i < 42; $i++) {
            DB::table('passions')->insert([
                'name' => $passions[$i],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // User to passions
        $passions = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        for ($i = 1; $i < 7; $i++) {
            for ($j = 0; $j < 5; $j++) {
                DB::table('user_to_passions')->insert([
                    'user_id' => $i,
                    'passion_id' => $passions[array_rand($passions, 1)],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        //filters type Event
        $mode = null;
        $name = array('All types', 'Paid', 'Free','All categories', 'Drink & Afterwork', 'Outdoors & Adventure', 'Music', 'Food Experiences', 'Sports',
                    'Talks & Workshops', 'Tech', 'Health & Wellnes', 'Activities & Games', 'Concerts & Festivals', 'Dance',
                    'Theater, Comedy & Shows', 'Fashions & Beauty', 'Romance', 'Dance',
                    'Computers', 'Networking', 'Business', 'Politics', 'Cooking', 'LGBTQ', 'Workout', 'Programming', 'Yoga');
        $type = 'Event';
        for ($i = 0; $i < 27; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $name[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => null,
            ]);
        }

        //filters type Advanced Love
        $mode = 'Love';
        $name = array('Looking for', 'Height', 'Exercise', 'Education level', 'Star Sign', 'Kids', 'Smoking', 'Drinking', 'Politics', 'Religion');
        $type = 'Advanced';
        for ($i = 0; $i < 10; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $name[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => null,
            ]);
        }

        //filters type Advanced Friendship
        $mode = 'Friendship';
        $name = array('Looking for', 'Relationship', 'Exercise', 'Star Sign', 'Kids', 'Smoking', 'Drinking', 'Religion');
        $type = 'Advanced';
        for ($i = 0; $i < 8; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $name[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => null,
            ]);
        }
        //filters type Advanced Networking
        $mode = 'Networking';
        $name = array('Looking for', 'Education level', 'Industry', 'Years of experience');
        $type = 'Advanced';
        for ($i = 0; $i < 4; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $name[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => null,
            ]);
        }

        // personal filter to filter
        $filter = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        for ($i = 1; $i < 7; $i++) {
            for ($j = 0; $j < 4; $j++) {
                DB::table('personal_filter_to_filters')->insert([
                    'filter_id' => $filter[array_rand($filter, 1)],
                    'personal_filter_id' => $i,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // promote event
        $number = array('1', '5', '10');
        $bool = array(0, 1);
        for ($i = 0; $i < 5; $i++) {
            DB::table('promote_events')->insert([
                'number' => $number[array_rand($number, 1)],
                'used' => 0,
                'active' => $bool[array_rand($bool, 1)],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => $i
            ]);
        }

        // event
        $type = array('Paid', 'Free');
        $privacy = array('Private', 'Public');
        $gender = array('Women', 'Men', 'Everyone');
        $recurrence = array('No Repeat', 'Daily', 'Weekly', 'Monthly');
        $bool = array(0, 1);
        $promote = array(0, 1, 2, 3, 4, 5);
        for ($i = 1; $i < 5; $i++) {
            for ($j = 0; $j < 2; $j++) {
                DB::table('events')->insert([
                    'name' => Str::random(10),
                    'date_time' => Carbon::now(),
                    'duration' => date("h:i:s"),
                    'privacy' => $privacy[array_rand($privacy, 1)],
                    'price' => rand(100, 99999),
                    'attendee_limit' => rand(10, 100),
                    'focused_on_gender' => $gender[array_rand($gender, 1)],
                    'focused_on_age_range' => '20-50',
                    'recurrence' => $recurrence[array_rand($recurrence, 1)],
                    'location' => Str::random(10),
                    'longitude' => rand(0, 10000),
                    'latitude' => rand(0, 10000),
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Assumenda, possimus! Nulla eaque nesciunt dolorum doloribus expedita perferendis officia soluta aliquam voluptate, consequatur placeat tenetur error quae, omnis dignissimos animi velit!',
                    'published' => $bool[array_rand($bool, 1)],
                    'user_id' => $i,
                    'promote_event_id' => $promote[array_rand($promote, 1)],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'type' => $type[array_rand($type, 1)],
                ]);
            }
        }

        // Photo events
        $name = "test-event.jpg";
        for ($i = 1; $i < 9; $i++) {
            DB::table('photo_events')->insert([
                'name' => $name,
                'path' => $name,
                'event_id' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Filter to event
        $filter = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        for ($i = 1; $i < 8; $i++) {
            for ($j = 0; $j < 4; $j++) {
                DB::table('filter_to_events')->insert([
                    'filter_id' => $filter[array_rand($filter, 1)],
                    'event_id' => $i,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // Report
        $type = array('Profile', 'Event');
        for ($i = 1; $i < 6; $i++) {
            $typeSelect = $type[array_rand($type, 1)];
            DB::table('reports')->insert([
                'type' => $typeSelect,
                'user_id' => $i,
                'profile_id' => $typeSelect == 'Profile' ? $i + 1 : null,
                'event_id' => $typeSelect == 'Event' ? $i + 1 : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Guest
        $status = array('Invited', 'Going', 'Maybe', 'Interested');
        for ($i = 1; $i < 6; $i++) {
            for ($j = 3; $j < 5; $j++) {
                DB::table('guests')->insert([
                    'status' => $status[array_rand($status, 1)],
                    'event_id' => $i,
                    'user_id' => $j,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // User contact
        $status = array('Unblock', 'Block', 'Other');
        for ($i = 1; $i < 9; $i++) {
            DB::table('user_contacts')->insert([
                'status' => $status[array_rand($status, 1)],
                'user_id' => $i,
                'contact_id' => $i + 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Matches
        $status = array(1, 2);
        for ($i = 1; $i < 9; $i++) {
            DB::table('matches')->insert([
                'status' => $status[array_rand($status, 1)],
                'user_one_id' => $i,
                'user_two_id' => $i + 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
