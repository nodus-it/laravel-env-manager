<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariableKey extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $userId = auth()->id();
            if ($userId !== null) {
                if ($model->created_by === null) {
                    $model->created_by = $userId;
                }
                if ($model->updated_by === null) {
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(function (self $model): void {
            $userId = auth()->id();
            if ($userId !== null) {
                $model->updated_by = $userId;
            }
        });
    }

    protected $fillable = [
        'key',
        'description',
        'type',
        'is_secret',
        'validation_rules',
        'default_value',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
            'default_value' => 'encrypted:string',
        ];
    }

    public function projectVariableValues(): HasMany
    {
        return $this->hasMany(ProjectVariableValue::class, 'variable_key_id');
    }

    public function environmentVariableValues(): HasMany
    {
        return $this->hasMany(EnvironmentVariableValue::class, 'variable_key_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
