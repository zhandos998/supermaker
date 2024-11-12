<?php

namespace Database\Seeders;

use App\Models\PaymentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentStatusSeeder extends Seeder
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
            PaymentStatus::create($status);
        }
    }
}
