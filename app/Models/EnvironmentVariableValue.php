<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvironmentVariableValue extends Model
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
        'environment_id',
        'variable_key_id',
        'value',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'encrypted:string',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function variableKey(): BelongsTo
    {
        return $this->belongsTo(VariableKey::class);
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
