<?php

namespace App\Filament\Resources\EnvironmentResource\Actions;

use App\Models\ProjectVariableValue;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

class AdoptAsProjectDefaultAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name ??= 'adoptAsProjectDefault';

        return parent::make($name)
            ->label(__('environment.effective_variables.actions.adopt_as_project_default'))
            ->icon('heroicon-o-arrow-up-on-square')
            ->visible(fn (Get $get): bool => (string) $get('source') === 'environment')
            ->action(function (Get $get, array $arguments = []): void {
                $projectId = (int) ($arguments['project_id'] ?? $get('project_id'));
                $variableKeyId = (int) ($arguments['variable_key_id'] ?? $get('variable_key_id'));
                $rawValue = (string) ($arguments['raw_value'] ?? $get('raw_value'));

                if ($projectId && $variableKeyId) {
                    ProjectVariableValue::query()->updateOrCreate(
                        [
                            'project_id' => $projectId,
                            'variable_key_id' => $variableKeyId,
                        ],
                        [
                            'value' => $rawValue,
                        ],
                    );

                    Notification::make()
                        ->title(__('environment.effective_variables.notifications.adopted_project_default'))
                        ->success()
                        ->send();
                }
            });
    }
}
