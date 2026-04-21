<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'      => 'Admin',
                'password'  => bcrypt('admin123'),
                'is_admin'  => true,
                'is_active' => true,
            ]
        );

        $this->call(ProductSeeder::class);
    }
}
