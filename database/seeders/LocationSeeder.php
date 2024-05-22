<?php

namespace Database\Seeders;

use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cities_translations')->truncate();
        DB::table('states_translations')->truncate();
        DB::table('countries_translations')->truncate();

        foreach (Country::get() as $country) {
            DB::table('countries_translations')->insertOrIgnore([
                'countries_id' => $country->id,
                'lang_code' => 'vi',
                'name' => $country->name,
            ]);
        }

        foreach (State::get() as $state) {
            DB::table('states_translations')->insertOrIgnore([
                'states_id' => $state->id,
                'lang_code' => 'vi',
                'name' => $state->name,
            ]);
        }

        foreach (City::get() as $city) {
            DB::table('cities_translations')->insertOrIgnore([
                'cities_id' => $city->id,
                'lang_code' => 'vi',
                'name' => $city->name,
            ]);
        }
    }
}
