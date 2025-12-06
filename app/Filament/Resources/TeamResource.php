<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers\ProjectsRelationManager;
use App\Filament\Resources\TeamResource\RelationManagers\UsersRelationManager;
use App\Models\Team;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamResource extends BaseResource
{
    protected static ?string $model = Team::class;

    protected static string|null|\UnitEnum $navigationGroup = NavigationGroup::SETTINGS;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label(__('fields.name'))
                ->required()
                ->maxLength(255),
            TextInput::make('slug')
                ->label(__('fields.slug'))
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Select::make('owner_id')
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
            TextEntry::make('name')->label(__('fields.name')),
            TextEntry::make('slug')->label(__('fields.slug')),
            TextEntry::make('owner.name')->label(__('fields.owner')),
            TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime(),
            TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('owner.name')
                    ->label(__('fields.owner'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('fields.members')),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProjectsRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'view' => Pages\ViewTeam::route('/{record}'),
        ];
    }
}
