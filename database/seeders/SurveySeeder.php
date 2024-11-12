<?php

namespace Database\Seeders;

use App\Models\Survey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            ['title' => 'Анкета', 'description' => 'Анкета для пользователей'],
        ];

        foreach ($stores as $store) {
            Survey::create($store);
        }
    }
}
