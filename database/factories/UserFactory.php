<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'password' => static::$password ??= Hash::make('Test@Password123!'),
            'level' => 'vendor',
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function superadmin(): static
    {
        return $this->state(fn () => ['level' => 'superadmin']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['level' => 'admin']);
    }

    public function vendor(): static
    {
        return $this->state(fn () => ['level' => 'vendor']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
