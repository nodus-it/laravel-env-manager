<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'repo_url',
        'description',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class);
    }

    public function projectVariableValues(): HasMany
    {
        return $this->hasMany(ProjectVariableValue::class);
    }
}
