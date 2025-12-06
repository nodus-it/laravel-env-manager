<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Environment;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class EnvironmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'environments';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label(__('fields.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->label(__('fields.slug'))
                ->required()
                ->maxLength(255)
                ->rules([
                    function ($record) {
                        return Rule::unique('environments', 'slug')
                            ->where('project_id', (int) $this->getOwnerRecord()->getKey())
                            ->ignore($record?->getKey());
                    },
                ]),
            Forms\Components\Select::make('type')
                ->label(__('fields.type'))
                ->options(function (): array {
                    return array_combine(Environment::TYPES, array_map(
                        fn (string $t): string => __('environment.types.'.$t),
                        Environment::TYPES
                    ));
                })
                ->required()
                ->default('custom')
                ->native(false),
            Forms\Components\Toggle::make('is_default')
                ->label(__('fields.is_default'))
                ->helperText(__('fields.environment_is_default_help'))
                ->default(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record): string => \App\Filament\Resources\EnvironmentResource::getUrl('view', ['record' => $record], shouldGuessMissingParameters: true))
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('environment.types.'.$state))
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'success',
                        'staging' => 'warning',
                        'testing' => 'info',
                        'local' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('fields.is_default'))
                    ->boolean(),
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
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('fields.type'))
                    ->options(array_combine(Environment::TYPES, Environment::TYPES)),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('fields.is_default')),
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
