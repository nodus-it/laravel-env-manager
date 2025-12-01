<?php

namespace App\Data;

use App\Models\Environment;
use App\Services\EnvironmentService;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class EnvironmentData extends Data
{
    public string $name;

    public string $slug;

    public string $type;

    public string $projectSlug;

    public string $projectName;

    /** @var Collection<int, EnvironmentKeyData> */
    public Collection $keys;

    public static function fromEnvironment(Environment $environment): self
    {
        return self::from([
            'name' => $environment->name,
            'slug' => $environment->slug,
            'type' => $environment->type,
            'projectName' => $environment->project->name,
            'projectSlug' => $environment->project->slug,
            'keys' => new EnvironmentService()->getKeys($environment),
        ]);
    }
}
