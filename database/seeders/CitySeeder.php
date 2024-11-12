<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Country;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Казахстан' => [
                'Алматы',
                'Нур-Султан',
                'Шымкент',
                'Актобе',
                'Караганда',
                'Атырау',
                'Павлодар',
                'Костанай',
                'Тараз',
                'Усть-Каменогорск',
                'Семей',
                'Уральск',
                'Кызылорда',
                'Темиртау',
                'Туркестан',
                'Кокшетау',
                'Рудный',
                'Актау',
                'Экибастуз',
                'Жезказган'
            ],
            // 'Россия' => ['Москва', 'Санкт-Петербург', 'Новосибирск'],
            // 'Беларусь' => ['Минск', 'Гомель', 'Могилев'],
            // 'Украина' => ['Киев', 'Одесса', 'Львов'],
            // 'Узбекистан' => ['Ташкент', 'Самарканд', 'Бухара'],
        ];

        foreach ($cities as $countryName => $cityNames) {
            $country = Country::where('name', $countryName)->first();
            foreach ($cityNames as $cityName) {
                City::create([
                    'name' => $cityName,
                    'country_id' => $country->id
                ]);
            }
        }
    }
}
