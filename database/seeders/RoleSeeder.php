<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Администратор', 'slug' => 'admin'],
            ['name' => 'Мастер', 'slug' => 'master'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
