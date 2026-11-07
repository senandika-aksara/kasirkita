<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'username' => Str::slug(Str::limit($name, 12, ''), '.').'.'.fake()->unique()->numberBetween(1, 9999),
            'password' => Str::password(16, symbols: false),
            'role' => UserRole::Cashier,
            'photo' => null,
            'status' => UserStatus::Active,
        ];
    }

    public function superadmin(): static
    {
        return $this->state(fn () => ['role' => UserRole::Superadmin]);
    }

    public function manager(): static
    {
        return $this->state(fn () => ['role' => UserRole::Manager]);
    }

    public function cashier(): static
    {
        return $this->state(fn () => ['role' => UserRole::Cashier]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => UserStatus::Inactive]);
    }
}
