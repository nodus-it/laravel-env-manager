<?php

namespace App\Filament\Resources;

use App\Enums\VariableKeySource;
use App\Facades\EnvironmentService;
use App\Filament\Resources\EnvironmentResource\Actions\AdoptAsDefaultAction;
use App\Filament\Resources\EnvironmentResource\Actions\AdoptAsProjectDefaultAction;
use App\Filament\Resources\EnvironmentResource\Actions\EditAtSourceAction;
use App\Filament\Resources\EnvironmentResource\Pages;
use App\Filament\Resources\EnvironmentResource\RelationManagers\PersonalAccessTokenRelationManager;
use App\Models\Environment;
use Filament\Forms;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class EnvironmentResource extends BaseResource
{
    protected static ?string $model = Environment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud';

    protected static string|\UnitEnum|null $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return __('models.navigation.organisation');
    }

    public static function getModelLabel(): string
    {
        return __('models.environment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.environment.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('project_id')
                ->label(__('models.project.label'))
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label(__('fields.name'))
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->label(__('fields.slug'))
                ->required()
                ->maxLength(255)
                ->rules([
                    function ($get, $record) {
                        return Rule::unique('environments', 'slug')
                            ->where('project_id', (int) $get('project_id'))
                            ->ignore($record?->getKey());
                    },
                ]),
            Forms\Components\TextInput::make('order')
                ->label(__('fields.order'))
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->required(),
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

    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            Infolists\Components\TextEntry::make('project.name')->label(__('models.project.label')),
            Infolists\Components\TextEntry::make('name')->label(__('fields.name')),
            Infolists\Components\TextEntry::make('slug')->label(__('fields.slug')),
            Infolists\Components\TextEntry::make('type')->label(__('fields.type')),
            Infolists\Components\IconEntry::make('is_default')->label(__('fields.is_default'))->boolean(),
            Infolists\Components\TextEntry::make('order')->label(__('fields.order')),
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime(self::dateTimeFormat()),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime(self::dateTimeFormat()),

            \Filament\Schemas\Components\Section::make(__('environment.effective_variables.title'))
                ->maxWidth(Width::Full)
                ->columnSpanFull()
                ->schema([
                    Infolists\Components\RepeatableEntry::make('effectiveVariables')
                        ->label('')
                        ->table([
                            Infolists\Components\RepeatableEntry\TableColumn::make(__('fields.key')),
                            Infolists\Components\RepeatableEntry\TableColumn::make(__('fields.type')),
                            Infolists\Components\RepeatableEntry\TableColumn::make(__('fields.value')),
                            Infolists\Components\RepeatableEntry\TableColumn::make(__('fields.source')),
                            Infolists\Components\RepeatableEntry\TableColumn::make(__('fields.actions')),
                        ])
                        ->state(function (Environment $environment): array {
                            return EnvironmentService::getKeys($environment)->toArray();
                        })
                        ->schema([
                            Infolists\Components\TextEntry::make('key')
                                ->label(__('fields.key'))
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('type')
                                ->label(__('fields.type'))
                                ->badge(),
                            Infolists\Components\TextEntry::make('value')
                                ->label(__('fields.value')),
                            Infolists\Components\TextEntry::make('source')
                                ->label(__('fields.source'))
                                ->formatStateUsing(function (string $state): string {
                                    return match ($state) {
                                        VariableKeySource::Environment->value => __('environment.effective_variables.source.environment'),
                                        VariableKeySource::Project->value => __('environment.effective_variables.source.project'),
                                        VariableKeySource::VariableKey->value => __('environment.effective_variables.source.default'),
                                    };
                                })
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'environment' => 'success',
                                    'project' => 'warning',
                                    default => 'gray',
                                }),
                            Infolists\Components\TextEntry::make('actions')
                                ->label('Aktionen')
                                ->suffixActions([
                                    EditAtSourceAction::make(),
                                    AdoptAsProjectDefaultAction::make(),
                                    AdoptAsDefaultAction::make(),
                                ]),
                        ])
                        ->contained(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('models.project.label'))
                    ->sortable()
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(self::dateTimeFormat())
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('fields.type'))
                    ->options(array_combine(Environment::TYPES, Environment::TYPES)),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('fields.is_default')),
            ])
            ->recordActions(self::defaultRecordActions())
            ->toolbarActions(self::defaultToolbarActions());
    }

    public static function getRelations(): array
    {
        return [
            PersonalAccessTokenRelationManager::class,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ViewEnvironment::route('/'),
            'view' => Pages\ViewEnvironment::route('/{record}'),
        ];
    }
}
