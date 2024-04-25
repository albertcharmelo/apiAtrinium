<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user
        $user = User::first();
        // Create an activity type
        $activityType = new ActivityType();
        $activityType->name = 'Activity Type Name';
        $activityType->description = 'Activity Type Description';

        $activityType->save();

        $user->company->activityTypes()->attach($activityType);
    }
}
