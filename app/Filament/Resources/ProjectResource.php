<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers\TeamsRelationManager;
use App\Models\Project;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('models.navigation.settings');
    }

    public static function getModelLabel(): string
    {
        return __('models.project.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.project.plural');
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
                Tables\Columns\TextColumn::make('repo_url')
                    ->label(__('fields.repo_url'))
                    ->limit(30)
                    ->url(fn ($record) => $record->repo_url, true),
                Tables\Columns\TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label(__('models.team.plural')),
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
            TeamsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
