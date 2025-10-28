<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'), // Change password if needed
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            echo "✅ Admin created successfully.\n";
        } else {
            echo "⚠️ Admin already exists.\n";
        }
    }
}
