<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('team_id')
                ->label(__('models.team.label'))
                ->relationship(name: 'teams', titleAttribute: 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('role')
                ->label(__('fields.role'))
                ->options([
                    'owner' => __('roles.owner'),
                    'contributor' => __('roles.contributor'),
                    'readonly' => __('roles.readonly'),
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.team.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('fields.slug'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label(__('fields.role'))
                    ->badge()
                    ->sortable(),
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
                Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label(__('models.team.label'))
                            ->options(\App\Models\Team::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('role')
                            ->label(__('fields.role'))
                            ->options([
                                'owner' => __('roles.owner'),
                                'contributor' => __('roles.contributor'),
                                'readonly' => __('roles.readonly'),
                            ])
                            ->required(),
                    ]),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make()
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label(__('fields.role'))
                                ->options([
                                    'owner' => __('roles.owner'),
                                    'contributor' => __('roles.contributor'),
                                    'readonly' => __('roles.readonly'),
                                ])
                                ->required(),
                        ]),
                    Actions\DetachAction::make(),
                ])->label(__('actions.group')),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
