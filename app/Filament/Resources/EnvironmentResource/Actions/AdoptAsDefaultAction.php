<?php

namespace App\Filament\Resources\EnvironmentResource\Actions;

use App\Models\VariableKey;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

class AdoptAsDefaultAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name ??= 'adoptAsDefault';

        return parent::make($name)
            ->label(__('environment.effective_variables.actions.adopt_as_default'))
            ->icon('heroicon-o-star')
            ->visible(fn (Get $get): bool => (string) $get('source') !== 'default')
            ->action(function (Get $get, array $arguments = []): void {
                $variableKeyId = (int) ($arguments['variable_key_id'] ?? $get('variable_key_id'));
                $rawValue = (string) ($arguments['raw_value'] ?? $get('raw_value'));

                if ($variableKeyId) {
                    VariableKey::query()->whereKey($variableKeyId)->update([
                        'default_value' => $rawValue,
                    ]);

                    Notification::make()
                        ->title(__('environment.effective_variables.notifications.adopted_default'))
                        ->success()
                        ->send();
                }
            });
    }
}
