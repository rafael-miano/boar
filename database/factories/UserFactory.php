<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * Philippine mobile number (09XX XXX XXXX format).
     */
    protected function philippinePhoneNumber(): string
    {
        return '09' . fake()->numerify('## ### ####');
    }

    /**
     * Address in Calape, Bohol, Philippines.
     */
    protected function calapeBoholAddress(): string
    {
        $barangays = [
            'Abucayan Norte', 'Abucayan Sur', 'Banlasan', 'Bentig', 'Binogawan', 'Bonbon',
            'Cabayugan', 'Cabudburan', 'Calunasan', 'Camias', 'Canguha', 'Catmonan',
            'Desamparados (Poblacion)', 'Kahayag', 'Kinabag-an', 'Labuon', 'Lawis', 'Liboron',
            'Lo-oc', 'Lomboy', 'Lucob', 'Madangog', 'Magtongtong', 'Mandaug', 'Mantatao',
            'Sampoangon', 'San Isidro', 'Santa Cruz (Poblacion)', 'Sohoton', 'Talisay',
            'Tinibgan', 'Tultugan', 'Ulbujan',
        ];
        $barangay = fake()->randomElement($barangays);
        $street = fake()->optional(0.7)->numerify('Purok #');
        $part = $street ? "{$street}, {$barangay}" : $barangay;
        return "{$part}, Calape, Bohol, Philippines";
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => $this->faker->randomElement(['boar-raiser', 'customer']),
            'phone_number' => $this->philippinePhoneNumber(),
            'address' => $this->calapeBoholAddress(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
