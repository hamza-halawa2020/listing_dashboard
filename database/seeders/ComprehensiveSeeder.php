<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ComprehensiveSeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone' => '1234567890',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'national_id' => '234324324',
            ]
        );
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'user',
                'phone' => '1234567891',
                'role' => 'member',
                'password' => Hash::make('12345678'),
                'national_id' => '12456',
            ]
        );
    }
}
