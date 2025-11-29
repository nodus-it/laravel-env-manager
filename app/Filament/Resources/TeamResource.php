<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers\ProjectsRelationManager;
use App\Filament\Resources\TeamResource\RelationManagers\UsersRelationManager;
use App\Models\Team;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('models.navigation.organisation');
    }

    public static function getModelLabel(): string
    {
        return __('models.team.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.team.plural');
    }

    public static function form(Schema $schema): Schema
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
                ->unique(ignoreRecord: true),
            Forms\Components\Select::make('owner_id')
                ->label(__('fields.owner'))
                ->relationship('owner', 'name')
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\TextEntry::make('name')->label(__('fields.name')),
            Infolists\Components\TextEntry::make('slug')->label(__('fields.slug')),
            Infolists\Components\TextEntry::make('owner.name')->label(__('fields.owner')),
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime('d.m.Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime('d.m.Y H:i'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label(__('fields.owner'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('fields.members')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                ])->label(__('actions.group')),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            ProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'view' => Pages\ViewTeam::route('/{record}'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
