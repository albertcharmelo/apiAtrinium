<?php

namespace Database\Seeders;

use App\Models\RoleRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a role request
        $user = User::first();

        $roleRequest = new RoleRequest();
        $roleRequest->current_role = $user->roles->first()->name;
        $roleRequest->requested_role = 'admin';
        $roleRequest->status = 'pending';
        $roleRequest->user()->associate($user);
        $roleRequest->save();
    }
}
