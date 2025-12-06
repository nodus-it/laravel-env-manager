<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\VariableKey;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class ProjectVariableValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'projectVariableValues';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('variable_key_id')
                ->label(__('fields.variable_key'))
                ->relationship('variableKey', 'key')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $set): void {
                    $set('value', null);
                })
                ->rules([
                    function ($record) {
                        return Rule::unique('project_variable_values', 'variable_key_id')
                            ->where('project_id', (int) $this->getOwnerRecord()->getKey())
                            ->ignore($record?->getKey());
                    },
                ]),
            Forms\Components\TextInput::make('value')
                ->label(__('fields.value'))
                ->password(function ($get): bool {
                    $vkId = $get('variable_key_id');
                    if (! $vkId) {
                        return false;
                    }

                    return (bool) optional(VariableKey::query()->find($vkId))->is_secret;
                })
                ->revealable(function ($get): bool {
                    $vkId = $get('variable_key_id');
                    if (! $vkId) {
                        return false;
                    }

                    return (bool) optional(VariableKey::query()->find($vkId))->is_secret;
                })
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record): string => \App\Filament\Resources\ProjectVariableValueResource::getUrl('view', ['record' => $record], shouldGuessMissingParameters: true))
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('variableKey.key')
                    ->label(__('fields.variable_key'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('fields.value'))
                    ->formatStateUsing(function ($record) {
                        return $record->variableKey?->is_secret ? '••••' : $record->value;
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_by.name')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('updatedBy.name')
                    ->sortable(),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                    Actions\DeleteAction::make(),
                ])->label(__('actions.group')),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
