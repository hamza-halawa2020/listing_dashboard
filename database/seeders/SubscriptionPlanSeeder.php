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
            ['name' => 'باقة Standard ( داخل المحافظة )', 'code' => 'IG', 'type' => 'individual', 'coverage_type' => 'governorate', 'price' => 300, 'duration_days' => 365, 'max_family_members' => 0],
            ['name' => 'باقة Standard ( داخل المحافظة )', 'code' => 'FG', 'type' => 'family', 'coverage_type' => 'governorate', 'price' => 600, 'duration_days' => 365, 'max_family_members' => 3],
            ['name' => 'باقة Premium ( جميع المحافظات )', 'code' => 'IA', 'type' => 'individual', 'coverage_type' => 'national', 'price' => 500, 'duration_days' => 365, 'max_family_members' => 0],
            ['name' => 'باقة Premium ( جميع المحافظات )', 'code' => 'FA', 'type' => 'family', 'coverage_type' => 'national', 'price' => 1000, 'duration_days' => 365, 'max_family_members' => 3],
        ];

        foreach ($plans as $plan) {
            \App\Models\SubscriptionPlan::create($plan);
        }
    }
}
