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
            'role' => 'admin',
            'phone_number' => '09171234567',
            'address' => 'Santa Cruz (Poblacion), Calape, Bohol, Philippines',
        ]);

        User::factory()->create([
            'name' => 'Boar Raiser',
            'email' => 'boar-raiser@a.com',
            'role' => 'boar-raiser',
            'phone_number' => '09181234567',
            'address' => 'Desamparados (Poblacion), Calape, Bohol, Philippines',
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'customer@a.com',
            'role' => 'customer',
            'phone_number' => '09191234567',
            'address' => 'Lo-oc, Calape, Bohol, Philippines',
        ]);

        User::factory()->create([
            'name' => 'Customer 2',
            'email' => 'customer2@a.com',
            'role' => 'customer',
            'email_verified_at' => null,
            'phone_number' => '09201234567',
            'address' => 'Mantatao, Calape, Bohol, Philippines',
        ]);

        User::factory(50)->create(['role' => 'boar-raiser']);
        User::factory(50)->create(['role' => 'customer']);
    }
}
