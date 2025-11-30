<?php

namespace App\Filament\Resources\EnvironmentResource\Actions;

use App\Filament\Resources\EnvironmentVariableValueResource;
use App\Filament\Resources\ProjectVariableValueResource;
use App\Filament\Resources\VariableKeyResource;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;

class EditAtSourceAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name ??= 'editAtSource';

        return parent::make($name)
            ->label(__('environment.effective_variables.actions.edit_at_source'))
            ->icon('heroicon-o-pencil-square')
            ->url(function (Get $get): ?string {
                $source = (string) $get('source');

                return match ($source) {
                    'environment' => $get('env_value_id')
                        ? EnvironmentVariableValueResource::getUrl('edit', ['record' => $get('env_value_id')], shouldGuessMissingParameters: true)
                        : null,
                    'project' => $get('project_value_id')
                        ? ProjectVariableValueResource::getUrl('edit', ['record' => $get('project_value_id')], shouldGuessMissingParameters: true)
                        : null,
                    'default' => $get('variable_key_id')
                        ? VariableKeyResource::getUrl('edit', ['record' => $get('variable_key_id')], shouldGuessMissingParameters: true)
                        : null,
                    default => null,
                };
            })
            ->visible(fn (Get $get): bool => in_array((string) $get('source'), ['environment', 'project', 'default'], true));
    }
}
