<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => 'KSRK-'.now()->format('md').'-'.fake()->unique()->numberBetween(100, 999),
            'table_number' => (string) fake()->numberBetween(1, 20),
            'user_id' => User::factory()->cashier(),
            'total_amount' => 0,
            'pay_amount' => 0,
            'change_amount' => 0,
            'payment_status' => PaymentStatus::Unpaid,
            'paid_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'pay_amount' => $attributes['total_amount'] ?? 0,
            'paid_at' => now(),
        ]);
    }
}
