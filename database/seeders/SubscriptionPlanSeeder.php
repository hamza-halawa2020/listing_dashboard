<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            ['name' => 'Individual Zone', 'code' => 'IZ', 'type' => 'individual', 'coverage_type' => 'zone', 'price' => 100, 'duration_days' => 365, 'max_family_members' => 0],
            ['name' => 'Individual Governorate', 'code' => 'IG', 'type' => 'individual', 'coverage_type' => 'governorate', 'price' => 200, 'duration_days' => 365, 'max_family_members' => 0],
            ['name' => 'Family Zone', 'code' => 'FZ', 'type' => 'family', 'coverage_type' => 'zone', 'price' => 300, 'duration_days' => 365, 'max_family_members' => 4],
            ['name' => 'Family Governorate', 'code' => 'FG', 'type' => 'family', 'coverage_type' => 'governorate', 'price' => 450, 'duration_days' => 365, 'max_family_members' => 4],
            ['name' => 'Individual National', 'code' => 'ISN', 'type' => 'individual', 'coverage_type' => 'national', 'price' => 500, 'duration_days' => 365, 'max_family_members' => 0],
            ['name' => 'Family National', 'code' => 'FSN', 'type' => 'family', 'coverage_type' => 'national', 'price' => 1000, 'duration_days' => 365, 'max_family_members' => 4],
        ];

        foreach ($plans as $plan) {
            \App\Models\SubscriptionPlan::create($plan);
        }
    }
}
