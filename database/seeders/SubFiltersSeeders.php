<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubFiltersSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // sub Love
        $mode = 'Love';
        $type = 'Advanced';
        $parent = array(28, 30, 31, 32, 33, 34, 35, 36, 37);

        $looking_for = array('Relationship', 'Something casual', "Don't know yet", 'Marriage');
        $type = 'Advanced';
        for ($i = 0; $i < 4; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $looking_for[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[0],
            ]);
        }
        $exercise = array('Active', 'Sometimes', 'Almost never');
        for ($i = 0; $i < 3; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $exercise[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[1],
            ]);
        }
        $education_level = array('High school', 'Trade/tech school', 'In college', 'Undergraduate degree', 'In grad school', 'Graduate degree');
        for ($i = 0; $i < 6; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $education_level[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[2],
            ]);
        }
        $start_sing = array('Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces');
        for ($i = 0; $i < 12; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $start_sing[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[3],
            ]);
        }
        $kids = array('Want someday', "Don't want", 'Have & want more', "Have & don't want more", 'Not sure yet');
        for ($i = 0; $i < 5; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $kids[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[4],
            ]);
        }
        $smoking = array('Socially', 'Never', 'Regularly');
        for ($i = 0; $i < 3; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $smoking[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[5],
            ]);
        }
        $drink = array('Socially', 'Never', 'Frequently', 'Sober');
        for ($i = 0; $i < 4; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $drink[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[6],
            ]);
        }
        $politics = array('Apolitical', 'Moderate', 'Liberal', 'Conservative');
        for ($i = 0; $i < 4; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $politics[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[7],
            ]);
        }
        $religion = array('Agnostic', 'Atheist', 'Buddhist', 'Catholic', 'Christian', 'Hindu', 'Jain', 'Jewish', 'Mormon', 'Muslim', 'Zoroastrian', 'Sikh', 'Spiritual', 'Other');
        for ($i = 0; $i < 14; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $religion[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[8],
            ]);
        }

        // sub Friendship
        $mode = 'Friendship';
        $type = 'Advanced';
        $parent = array(38, 39, 40, 41, 42, 43, 44, 45);

        $looking_for = array('Gaming', 'Volunteer', 'Workouts & Sports', 'Travel', 'Live music', 'Nights out', 'Coworking', 'Faith studies', 'Arts & Culture', 'Roommate', 'Kid playdates', 'Anything');
        for ($i = 0; $i < 12; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $looking_for[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[0],
            ]);
        }
        $relationship = array('Single', 'In a relationship', 'Engaged', 'Married', "It's complicated", 'Divorced', 'Widowed');
        for ($i = 0; $i < 7; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $relationship[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[1],
            ]);
        }
        $exercise = array('Active', 'Sometimes', 'Almost never');
        for ($i = 0; $i < 3; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $exercise[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[2],
            ]);
        }
        $start_sing = array('Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces');
        for ($i = 0; $i < 12; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $start_sing[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[3],
            ]);
        }
        $kids = array('No kids', 'Expecting', 'New parent', 'Toddler(s)', ' School age', 'Tween(s)', 'Teen(s)', 'College', 'Grown');
        for ($i = 0; $i < 9; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $kids[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[4],
            ]);
        }
        $smoking = array('Socially', 'Never', 'Regularly');
        for ($i = 0; $i < 3; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $smoking[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[5],
            ]);
        }
        $drink = array('Socially', 'Never', 'Frequently', 'Sober');
        for ($i = 0; $i < 4; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $drink[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[6],
            ]);
        }
        $religion = array('Agnostic', 'Atheist', 'Buddhist', 'Catholic', 'Christian', 'Hindu', 'Jain', 'Jewish', 'Mormon', 'Muslim', 'Zoroastrian', 'Sikh', 'Spiritual', 'Other');
        for ($i = 0; $i < 14; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $religion[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[7],
            ]);
        }

        // sub Friendship
        $mode = 'Networking';
        $type = 'Advanced';
        $parent = array(46, 47, 48, 49);

        $looking_for = array('Investment/investor', 'Mentee/mentor', 'Internship', 'Networking', 'Freelance', 'Part-time job', 'Full-time job', 'People to hire');
        for ($i = 0; $i < 8; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $looking_for[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[0],
            ]);
        }
        $education_level = array('High school', 'Trade/tech school', 'In college', 'Undergraduate degree', 'In grad school', 'Graduate degree');
        for ($i = 0; $i < 6; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $education_level[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[1],
            ]);
        }
        $industry = array('Tech', 'Arts & Entertainment', 'Banking', 'Consulting', 'Creatives', 'Media & Journalism', 'Music', 'VC & Investment', 'Fashion', 'Education & Academia', 'Government & Politics', 'Sales', 'Marketing',
                        'PR', 'Advertising', 'Real estate', 'Insurace', 'Law & Policy', 'Counselling', 'Medicine', 'Police & Military', 'Construction', 'Food & Beverage', 'Travel & Hospitality',
                        'Manufacturing', 'Other');
        for ($i = 0; $i < 26; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $industry[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[2],
            ]);
        }
        $years_of_exp = array('0-2 years', '3-5 years', '6-10 years', '10+ years', '20+ years');
        for ($i = 0; $i < 5; $i++) {
            DB::table('filters')->insert([
                'type' => $type,
                'name' => $years_of_exp[$i],
                'mode' => $mode,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'parent' => $parent[3],
            ]);
        }
    }
}
