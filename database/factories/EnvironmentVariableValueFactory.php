<?php

namespace Database\Factories;

use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\User;
use App\Models\VariableKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnvironmentVariableValue>
 */
class EnvironmentVariableValueFactory extends Factory
{
    protected $model = EnvironmentVariableValue::class;

    public function definition(): array
    {
        return [
            'environment_id' => Environment::factory(),
            'variable_key_id' => VariableKey::factory(),
            'value' => (string) fake()->word(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
