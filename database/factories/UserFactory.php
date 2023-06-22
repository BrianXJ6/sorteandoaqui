<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cpf' => fake()->cpf(false),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->cellphoneNumber(false),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => '123123123',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverifiedMail(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    /**
     * Indicate that the model's phone should be unverified.
     *
     * @return static
     */
    public function unverifiedPhone(): static
    {
        return $this->state(fn () => ['phone_verified_at' => null]);
    }
}
