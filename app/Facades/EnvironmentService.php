<?php

namespace App\Facades;

use App\Data\EnvironmentKeyData;
use App\Models\Environment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection<int, EnvironmentKeyData> getKeys(Environment $environment, bool $showSecrets = false)
 * @method static void setKey(Environment $environment, string $key, mixed $value, \App\Enums\VariableKeySource $source)
 */
class EnvironmentService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'environment.service';
    }
}
