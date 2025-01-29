<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'date' => Carbon::now(),
            'description' => $this->faker->text(),
            'amount' => $this->faker->randomNumber(6),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
