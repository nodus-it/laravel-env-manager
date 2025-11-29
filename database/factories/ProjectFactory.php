<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::random(6),
            'repo_url' => $this->faker->optional()->url(),
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
