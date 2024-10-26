<?php

namespace Database\Seeders;

use App\Models\Variable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variables = [
            // ['name' => 'default_video_count', 'variable' => '12'],
        ];

        foreach ($variables as $variable) {
            Variable::create($variable);
        }
    }
}
