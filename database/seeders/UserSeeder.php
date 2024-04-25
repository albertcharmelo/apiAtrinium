<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user
        $user = new User();
        $user->name = 'Albert Charmelo';
        $user->email = 'acharmelo99@gmail.com';
        $user->password = bcrypt('123456789');
        $user->phone = '123456789';
        $user->address = 'User Address';
        $user->save();

        $user->assignRole(Role::findByName('user', 'api'));
    }
}
