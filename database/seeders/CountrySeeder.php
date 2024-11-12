<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Казахстан'],
            // ['name' => 'Россия'],
            // ['name' => 'Беларусь'],
            // ['name' => 'Украина'],
            // ['name' => 'Узбекистан'],
            // ['name' => 'Киргизия'],
            // ['name' => 'Таджикистан'],
            // ['name' => 'Армения'],
            // ['name' => 'Азербайджан'],
            // ['name' => 'Молдова'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
