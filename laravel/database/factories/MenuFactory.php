<?php

namespace Database\Factories;

use App\Enums\MenuStatus;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'MNU-'.sprintf('%03d', fake()->unique()->numberBetween(1, 999)),
            'category_id' => Category::factory(),
            'name' => ucwords(fake()->words(2, true)),
            'price' => fake()->numberBetween(5, 50) * 1000,
            'image' => null,
            'status' => MenuStatus::Available,
        ];
    }

    public function soldOut(): static
    {
        return $this->state(fn () => ['status' => MenuStatus::SoldOut]);
    }
}
