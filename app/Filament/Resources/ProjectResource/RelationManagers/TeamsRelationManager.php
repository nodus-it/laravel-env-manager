<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
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
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label(__('relations.linked'))
                    ->dateTime('d.m.Y H:i'),
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
