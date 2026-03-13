<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure to truncate the users table if you want a fresh start
        DB::table('users')->truncate();
        
        $password = Hash::make('password');

        $users = [
            [
                'name' => 'Admin',
                'email' => 'dasatti@gmail.com',
                'password' => $password,
                'created_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            // Check if the user already exists
            if (!User::where('email', $userData['email'])->exists()) {
                // Create the user if they don't exist
                $user = User::create($userData);

                // Assign role to super admin
                if ($user->email === 'dasatti@gmail.com') {
                    $role = Role::find(1); // Assuming Role ID 1 exists
                    $user->assignRole($role);
                }
            }
        }
    }
}
