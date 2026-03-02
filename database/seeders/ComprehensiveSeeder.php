<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Review;
use App\Models\Post;
use App\Models\MainSlider;
use App\Models\Contact;
use App\Models\Course;
use App\Models\MediaCenter;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class ComprehensiveSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'national_id' => '234324324',
                'membership_card_number' => '3224324',
            ]
        );
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'user',
                'role' => 'member',
                'password' => Hash::make('12345678'),
                'national_id' => '12456',
                'membership_card_number' => '12345',
            ]
        );

        // 2. Create Site Settings
        $settings = [
            'phone' => '+201234567890',
            'whatsapp' => '+201234567890',
            'facebook' => 'https://facebook.com/bayaanacademy',
            'instagram' => 'https://instagram.com/bayaanacademy',
            'email' => 'info@bayaan-academy.com',
            'address' => 'Cairo, Egypt - Online via Zoom Worldwide',
            'about_us' => 'Bayaan Academy is an online educational platform specializing in teaching the Holy Quran, Arabic language, and Islamic studies for both native and non-native speakers. We aim to spread the light of revelation using the latest educational methods.',
            'about_us_footer' => 'A leading academy in Quranic sciences and Arabic language, combining quality education with the honesty of communication.',
            'logo' => 'settings/default-logo.png',
            'privacy_policy' => '<h1>Privacy Policy</h1><p>We respect your privacy...</p>',
            'terms_conditions' => '<h1>Terms & Conditions</h1><p>By using our services...</p>',
        ];

        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }

        // 4. Create Posts
        $posts = [
            [
                'title' => 'Best Ways to Memorize and Retain the Quran',
                'description' => 'In this article, we review 5 proven strategies that help you memorize the Quran quickly while ensuring you don\'t forget, focusing on spaced repetition and understanding before memorizing.',
                'image' => 'posts/hifz-tips.jpg',
                'status' => 1,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Why Should Our Children Learn Arabic?',
                'description' => 'Arabic is the language of the Quran and the key to understanding religion. We discuss the challenges parents face abroad and how to make the language lovable for their children.',
                'image' => 'posts/why-arabic.jpg',
                'status' => 1,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'The Impact of Tajweed in Pondering over Allah\'s Verses',
                'description' => 'Tajweed is not just beautiful sounds; it is giving every letter its right, which helps the reader to contemplate, have humility, and understand Allah\'s intent.',
                'image' => 'posts/tajweed-impact.jpg',
                'status' => 1,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($posts as $post) {
            Post::create($post);
        }


        // 6. Create Reviews
        $reviews = [
            [
                'created_by' => $admin->id,
                'review' => 'Alhumdulillah, my son started reading from the Mushaf correctly after only 3 months of the Noor Al-Bayan course. The teacher is very patient and highly skilled.',
                'status' => 1,
                'approved_by' => $admin->id,
            ],
            [
                'created_by' => $admin->id,
                'review' => 'Wonderful academy, classes are regular and the curriculum is clear. They helped me a lot in correcting my recitation and I am now on my way to obtaining the Ijazah.',
                'status' => 1,
                'approved_by' => $admin->id,
            ],
            [
                'created_by' => $admin->id,
                'review' => 'I was worried about my children\'s language, but with Bayaan Academy they found the right environment to learn Arabic and Quran as if they were in an Arabic-speaking country.',
                'status' => 1,
                'approved_by' => $admin->id,
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }


        // 8. Create Contacts (Sample)
        $contacts = [
            [
                'name' => 'Yasser Al-Qahtani',
                'phone' => '+966500000000',
                'message' => 'Assalamu Alaikum, do you have available slots in the evening for Quran memorization for kids?',
            ],
             [
                'name' => 'Mona Al-Sayer',
                'phone' => '+447000000000',
                'message' => 'I want to inquire about the fees and duration for the Arabic for Non-Native Speakers course.',
            ],
        ];

        foreach ($contacts as $contact) {
            Contact::create($contact);
        }

        echo "created successfully in English!\n";
    }
}