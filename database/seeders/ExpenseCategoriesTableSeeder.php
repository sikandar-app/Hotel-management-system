<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        $categories = [
            [
                'name' => 'Salary',
                'status' => 'active',
            ],
            [
                'name' => 'Utility',
                'status' => 'active',
            ],
            [
                'name' => 'Maintenance',
                'status' => 'active',
            ],
            [
                'name' => 'Miscellaneous',
                'status' => 'active',
            ]
        ];

        foreach ($categories as $category) {
            // Check if the user already exists
            if (!DB::table('expense_categories')->where('name', $category['name'])->exists()) {
                // Create the user if they don't exist
                DB::table('expense_categories')->insert($category);
            }
        }
       
    }
}
