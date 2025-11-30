<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VariableKeyResource\Pages;
use App\Models\VariableKey;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VariableKeyResource extends Resource
{
    protected static ?string $model = VariableKey::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    public static function getNavigationGroup(): ?string
    {
        return __('models.navigation.organisation');
    }

    public static function getModelLabel(): string
    {
        return __('models.variable_key.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.variable_key.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('key')
                ->label(__('fields.key'))
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Forms\Components\Textarea::make('description')
                ->label(__('fields.description'))
                ->rows(3)
                ->nullable(),
            Forms\Components\Select::make('type')
                ->label(__('fields.type'))
                ->options([
                    'string' => 'string',
                    'int' => 'int',
                    'bool' => 'bool',
                    'json' => 'json',
                ])
                ->required()
                ->native(false),
            Forms\Components\Toggle::make('is_secret')
                ->label(__('fields.is_secret'))
                ->default(false)
                ->live(),
            Forms\Components\Textarea::make('validation_rules')
                ->label(__('fields.validation_rules'))
                ->rows(3)
                ->nullable(),
            Forms\Components\TextInput::make('default_value')
                ->label(__('fields.default_value'))
                ->password(fn ($get) => (bool) $get('is_secret'))
                ->revealable(fn ($get) => (bool) $get('is_secret'))
                ->nullable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\TextEntry::make('key')->label(__('fields.key')),
            Infolists\Components\TextEntry::make('description')->label(__('fields.description')),
            Infolists\Components\TextEntry::make('type')->label(__('fields.type')),
            Infolists\Components\IconEntry::make('is_secret')->label(__('fields.is_secret'))->boolean(),
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime('d.m.Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime('d.m.Y H:i'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label(__('fields.key'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('fields.type'))
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_secret')
                    ->label(__('fields.is_secret'))
                    ->boolean(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVariableKeys::route('/'),
            'create' => Pages\CreateVariableKey::route('/create'),
            'view' => Pages\ViewVariableKey::route('/{record}'),
            'edit' => Pages\EditVariableKey::route('/{record}/edit'),
        ];
    }
}
