<?php

namespace App\Services;

use App\Data\EnvironmentKeyData;
use App\Enums\VariableKeySource;
use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\ProjectVariableValue;
use App\Models\VariableKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EnvironmentService
{
    /**
     * Get effective keys for an environment.
     *
     * @return Collection<int, EnvironmentKeyData>
     */
    public function getKeys(Environment $environment, bool $showSecrets = false): Collection
    {
        $variableKeys = VariableKey::query()
            ->select(['id', 'key', 'type', 'is_secret', 'default_value'])
            ->orderBy('key')
            ->get();

        $projectDefaults = ProjectVariableValue::query()
            ->where('project_id', $environment->project_id)
            ->get()
            ->keyBy('variable_key_id');

        $environmentKeys = EnvironmentVariableValue::query()
            ->where('environment_id', $environment->id)
            ->get()
            ->keyBy('variable_key_id');

        $rows = [];

        foreach ($variableKeys as $variableKey) {
            $source = null;
            $value = null;
            $sourceId = null;

            if (isset($environmentKeys[$variableKey->id])) {
                $source = VariableKeySource::Environment;
                $value = $environmentKeys[$variableKey->id]->value;
                $sourceId = $environment->id;
            } elseif (isset($projectDefaults[$variableKey->id])) {
                $source = VariableKeySource::Project;
                $value = $projectDefaults[$variableKey->id]->value;
                $sourceId = $projectDefaults[$variableKey->id]->id;
            } elseif ($variableKey->default_value !== null && $variableKey->default_value !== '') {
                $source = VariableKeySource::VariableKey;
                $value = $variableKey->default_value;
                $sourceId = $variableKey->id;
            }

            if ($source === null) {
                continue;
            }

            $value = ($variableKey->is_secret && ! $showSecrets) ? '••••' : (string) $value;

            $rows[] = EnvironmentKeyData::from([
                'variable_key_id' => $variableKey->id,
                'key' => $variableKey->key,
                'type' => $variableKey->type,
                'value' => $value,
                'source' => $source,
                'sourceId' => $sourceId,
            ]);
        }

        return collect($rows);
    }

    /**
     * Persist a key's value at the given source level.
     */
    public function setKey(Environment $environment, string $key, mixed $value, VariableKeySource $source): void
    {
        $variableKey = VariableKey::query()->where('key', $key)->first();
        if($variableKey === null) {
            $variableKey = VariableKey::create([
                'key' => $key,
                'type' => 'string',
            ]);
        }

        if ($source === VariableKeySource::Environment) {
            $this->upsertEnvironmentValue($environment, $variableKey->id, $value);
        } elseif ($source === VariableKeySource::Project) {
            // When setting a project-level default, remove any environment-specific value to let the project default take effect.
            EnvironmentVariableValue::query()
                ->where('environment_id', $environment->id)
                ->where('variable_key_id', $variableKey->id)
                ->delete();

            $this->upsertProjectDefault($environment->project_id, $variableKey->id, $value);
        } else { // VariableKeySource::VariableKey (global default)
            // When setting a global default, remove any project or environment values so the global default applies.
            EnvironmentVariableValue::query()
                ->where('environment_id', $environment->id)
                ->where('variable_key_id', $variableKey->id)
                ->delete();

            ProjectVariableValue::query()
                ->where('project_id', $environment->project_id)
                ->where('variable_key_id', $variableKey->id)
                ->delete();

            $this->updateGlobalDefault($variableKey, $value);
        }
    }

    /**
     * Persist multiple keys atomically within a transaction.
     *
     * @param  array<int, array{key:string, value:mixed, source:string|VariableKeySource}>  $items
     */
    public function setKeys(Environment $environment, array $items): void
    {
        DB::transaction(function () use ($environment, $items): void {
            foreach ($items as $item) {
                $source = $item['source'] instanceof VariableKeySource
                    ? $item['source']
                    : VariableKeySource::from((string) $item['source']);

                $this->setKey(
                    environment: $environment,
                    key: (string) $item['key'],
                    value: $item['value'] ?? null,
                    source: $source,
                );
            }
        });
    }

    protected function upsertEnvironmentValue(Environment $environment, int $variableKeyId, mixed $value): void
    {
        EnvironmentVariableValue::query()->updateOrCreate(
            [
                'environment_id' => $environment->id,
                'variable_key_id' => $variableKeyId,
            ],
            [
                'value' => is_null($value) ? '' : (string) $value,
            ],
        );
    }

    protected function upsertProjectDefault(int $projectId, int $variableKeyId, mixed $value): void
    {
        ProjectVariableValue::query()->updateOrCreate(
            [
                'project_id' => $projectId,
                'variable_key_id' => $variableKeyId,

            ],
            [
                'value' => is_null($value) ? '' : (string) $value,
            ],
        );
    }

    protected function updateGlobalDefault(VariableKey $variableKey, mixed $value): void
    {
        $variableKey->forceFill([
            'default_value' => is_null($value) ? '' : (string) $value,
        ])->save();
    }
}
