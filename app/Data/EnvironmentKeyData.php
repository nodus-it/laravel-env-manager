<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class EnvironmentKeyData extends Data
{
    public string $key;

    public string $value;

    public string $source;
}
