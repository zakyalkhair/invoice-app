<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Mitra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 50000, 500000);
        
        return [
            'user_id' => User::factory(),
            'mitra_id' => Mitra::factory(),
            'invoice_number' => 'INV-' . date('Y') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'received_from' => $this->faker->company(),
            'issuer_name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'total_amount' => $totalAmount,
            'amount_in_words' => $this->faker->words(5, true),
            'invoice_date' => now(),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'approved', 'rejected']),
        ];
    }
}
