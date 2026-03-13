<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure to truncate the users table if you want a fresh start
        DB::table('settings')->truncate();
        
        $settings = [
            [
                'name' => 'The Elitestays',
                'email' => 'dasatti@gmail.com',
                'phone' => '1234567890',
                'facebook_link' => 'https://www.facebook.com',
                'twitter_link' => 'https://www.twitter.com',
                'instagram_link' => 'https://www.instagram.com',
                'youtube_link' => 'https://www.youtube.com',
                'address' => '123 Main Street, City, Country',
                'advance_payment_taken_in_percentage' => 30,
                'created_at' => now(),
            ],
        ];

        foreach ($settings as $settingData) {
            // Check if the user already exists
            if (!DB::table('settings')->where('email', $settingData['email'])->exists()) {
                // Create the user if they don't exist
                DB::table('settings')->insert($settingData);
            }
        }
       
    }
}
