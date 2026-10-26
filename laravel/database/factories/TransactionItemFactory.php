<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(5, 50) * 1000;
        $qty = fake()->numberBetween(1, 3);

        return [
            'transaction_id' => Transaction::factory(),
            'menu_id' => Menu::factory(),
            'menu_name' => ucwords(fake()->words(2, true)),
            'price' => $price,
            'qty' => $qty,
            'subtotal' => $price * $qty,
        ];
    }
}
