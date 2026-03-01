<?php

namespace Database\Seeders;

use App\Models\Category;
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

        foreach ($categories as $categoryName => $subCategories) {
            $category = Category::create([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'created_by' => 1, // assuming admin user has id 1
            ]);

            foreach ($subCategories as $subCategoryName) {
                Category::create([
                    'name' => $subCategoryName,
                    'slug' => Str::slug($subCategoryName),
                    'parent_id' => $category->id,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
