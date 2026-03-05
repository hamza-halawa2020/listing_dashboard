<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'مستشفى' => [],
            'عيادات تخصصية' => [],
            'مركز طبى' => [],
            'عيادات' => [],
            'حقن مجهرى' => [],
            'تجميل' => [],
            'علاج اورام' => [],
            'تفتيت حصوات' => [],
            'صدر ورعاية' => [],
            'اجهزة تعويضية' => [],
            'انف واذن' => [],
            'تنمية فكرية' => [],
            'عيون وليزك' => [
                'مركز رمد',
                'عيون اطفال',
            ],
            'صيدلية' => [],
            'اشعة' => [],
            'تحاليل' => [],
            'اسنان' => [],
            'علاج طبيعى' => [],
            'مسالك' => [],
            'مخ واعصاب' => [],
            'نفسية وعصبية' => [],
            'قلب وقسطرة' => [],
            'باطة وكلى' => [],
            'نساء وتوليد' => [
                'توليد',
            ],
            'جراحة ومناظير' => [],
            'بصريات' => [],
        ];

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'phone' => '1234567892',
                'role' => 'admin',
            ]
        );

        foreach ($categories as $categoryName => $subCategories) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                [
                    'slug' => Str::slug($categoryName) ?: Str::random(10),
                    'created_by' => $admin->id,
                ]
            );

            foreach ($subCategories as $subCategoryName) {
                Category::firstOrCreate(
                    ['name' => $subCategoryName],
                    [
                        'slug' => Str::slug($subCategoryName) ?: Str::random(10),
                        'parent_id' => $category->id,
                        'created_by' => $admin->id,
                    ]
                );
            }
        }
    }
}
