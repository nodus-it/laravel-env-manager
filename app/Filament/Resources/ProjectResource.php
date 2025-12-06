<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\EnvironmentsRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectVariableValuesRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TeamsRelationManager;
use App\Models\Project;
use Filament\Forms;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends BaseResource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::MAIN;

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
            Forms\Components\TextInput::make('repo_url')
                ->label(__('fields.repo_url'))
                ->url()
                ->maxLength(255)
                ->nullable(),
            Forms\Components\Textarea::make('description')
                ->label(__('fields.description'))
                ->rows(5)
                ->nullable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\TextEntry::make('name')->label(__('fields.name')),
            Infolists\Components\TextEntry::make('slug')->label(__('fields.slug')),
            Infolists\Components\TextEntry::make('repo_url')->label(__('fields.repo_url'))->url(fn ($record) => $record->repo_url, true),
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime(),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime(),
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
                Tables\Columns\TextColumn::make('repo_url')
                    ->label(__('fields.repo_url'))
                    ->limit(30)
                    ->url(fn ($record) => $record->repo_url, true),
                Tables\Columns\TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label(__('models.team.plural')),
                Tables\Columns\IconColumn::make('is_secret')
                    ->label(__('fields.is_secret'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updatedBy.name')
                    ->sortable(),
            ])
            ->recordActions(self::defaultRecordActions())
            ->toolbarActions(self::defaultToolbarActions());
    }

    public static function getRelations(): array
    {
        return [
            EnvironmentsRelationManager::class,
            ProjectVariableValuesRelationManager::class,
            TeamsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProject::route('/{record}'),
        ];
    }
}
