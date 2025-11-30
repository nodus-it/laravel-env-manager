<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectVariableValue;
use App\Models\User;
use App\Models\VariableKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectVariableValue>
 */
class ProjectVariableValueFactory extends Factory
{
    protected $model = ProjectVariableValue::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'variable_key_id' => VariableKey::factory(),
            'value' => (string) fake()->word(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
