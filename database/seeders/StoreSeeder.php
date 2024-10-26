<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            ['user_id' => '2', 'name' => 'Store', 'company_type_id' => '1'],
        ];

        foreach ($stores as $store) {
            Store::create($store);
        }
    }
}
