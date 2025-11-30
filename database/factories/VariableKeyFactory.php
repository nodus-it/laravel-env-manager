<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VariableKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VariableKey>
 */
class VariableKeyFactory extends Factory
{
    protected $model = VariableKey::class;

    public function definition(): array
    {
        $key = Str::upper(Str::snake(fake()->unique()->words(2, true)));

        return [
            'key' => $key,
            'description' => fake()->optional()->sentence(8),
            'type' => fake()->randomElement(['string', 'int', 'bool', 'json']),
            'is_secret' => fake()->boolean(40),
            'validation_rules' => null,
            'default_value' => fake()->optional()->word(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
