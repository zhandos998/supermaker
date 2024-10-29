<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::where('slug','admin')->first();
        $master = Role::where('slug', 'master')->first();

        $user1 = new User();
        // $user1->fio = 'admin';
        $user1->username = 'admin';

        $user1->firstname = 'admin';
        $user1->lastname = 'admin';

        $user1->email = 'admin';
        $user1->phone = '87771234567';
        $user1->iin = '111111111111';
        $user1->city_id = 1;

        $user1->password = bcrypt('123456');
        $user1->save();
        $user1->roles()->attach($admin);

        $user2 = new User();
        // $user2->fio = 'Master';
        $user2->username = 'Master';

        $user2->firstname = 'Master';
        $user2->lastname = 'Master';

        $user2->email = 'master';
        $user2->phone = '87771234568';
        $user2->iin = '111111111112';
        $user2->city_id = 1;

        $user2->password = bcrypt('123456');
        $user2->save();
        $user2->roles()->attach($master);
    }
}
