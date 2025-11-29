<?php

namespace App\Filament\Resources\TeamResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('user_id')
                ->label(__('models.user.label'))
                ->relationship(name: 'users', titleAttribute: 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('role')
                ->label(__('fields.role'))
                ->options([
                    'owner' => __('roles.owner'),
                    'admin' => __('roles.admin'),
                    'member' => __('roles.member'),
                    'viewer' => __('roles.viewer'),
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.user.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label(__('fields.role'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label(__('relations.added'))
                    ->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label(__('models.user.label'))
                            ->options(\App\Models\User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('role')
                            ->label(__('fields.role'))
                            ->options([
                                'owner' => __('roles.owner'),
                                'admin' => __('roles.admin'),
                                'member' => __('roles.member'),
                                'viewer' => __('roles.viewer'),
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
                                    'admin' => __('roles.admin'),
                                    'member' => __('roles.member'),
                                    'viewer' => __('roles.viewer'),
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
