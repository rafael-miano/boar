<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@a.com',
            'role' => 'admin'
        ]);


        User::factory()->create([
            'name' => 'Boar Raiser',
            'email' => 'boar-raiser@a.com',
            'role' => 'boar-raiser'
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@a.com',
            'role' => 'customer'
        ]);

        User::factory()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@a.com',
            'role' => 'customer',
            'email_verified_at' => null,
        ]);
    }
}
