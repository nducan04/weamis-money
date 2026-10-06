<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'type' => 'user',
            'owner_type' => 'App\Models\User',
            'owner_id' => 1,
            'name' => $this->faker->name,
            'balance' => 0,
        ];
    }
}
