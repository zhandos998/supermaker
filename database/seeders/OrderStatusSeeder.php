<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Completed'],
            ['name' => 'Pending'],
            ['name' => 'Failed'],
            // Добавьте другие статусы, если необходимо
        ];

        foreach ($statuses as $status) {
            OrderStatus::create($status);
        }
    }
}
