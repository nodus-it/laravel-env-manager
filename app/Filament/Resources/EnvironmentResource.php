<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvironmentResource\Pages;
use App\Models\Environment;
use App\Models\EnvironmentVariableValue;
use App\Models\ProjectVariableValue;
use App\Models\VariableKey;
use App\Filament\Resources\EnvironmentVariableValueResource;
use App\Filament\Resources\ProjectVariableValueResource;
use App\Filament\Resources\VariableKeyResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use App\Filament\Resources\EnvironmentResource\Actions\EditAtSourceAction;
use App\Filament\Resources\EnvironmentResource\Actions\AdoptAsProjectDefaultAction;
use App\Filament\Resources\EnvironmentResource\Actions\AdoptAsDefaultAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EnvironmentResource extends Resource
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
            Infolists\Components\TextEntry::make('created_at')->label(__('timestamps.created_at'))->dateTime('d.m.Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')->label(__('timestamps.updated_at'))->dateTime('d.m.Y H:i'),

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
                        ])
                        ->state(function (Environment $record): array {
                            // Build effective variables for this environment: env override > project default > key default
                            $projectId = $record->project_id;
                            $envId = $record->getKey();

                            $variableKeys = \App\Models\VariableKey::query()
                                ->select(['id', 'key', 'type', 'is_secret', 'default_value'])
                                ->orderBy('key')
                                ->get();

                            $projectDefaults = \App\Models\ProjectVariableValue::query()
                                ->where('project_id', $projectId)
                                ->get()
                                ->keyBy('variable_key_id');

                            $envOverrides = \App\Models\EnvironmentVariableValue::query()
                                ->where('environment_id', $envId)
                                ->get()
                                ->keyBy('variable_key_id');

                            $rows = [];
                            foreach ($variableKeys as $vk) {
                                $source = null;
                                $value = null;
                                $rawValue = null;
                                $envValueId = null;
                                $projectValueId = null;

                                if (isset($envOverrides[$vk->id])) {
                                    $source = 'environment';
                                    $value = $envOverrides[$vk->id]->value;
                                    $rawValue = $value;
                                    $envValueId = $envOverrides[$vk->id]->getKey();
                                } elseif (isset($projectDefaults[$vk->id])) {
                                    $source = 'project';
                                    $value = $projectDefaults[$vk->id]->value;
                                    $rawValue = $value;
                                    $projectValueId = $projectDefaults[$vk->id]->getKey();
                                } elseif ($vk->default_value !== null && $vk->default_value !== '') {
                                    $source = 'default';
                                    $value = $vk->default_value;
                                    $rawValue = $value;
                                }

                                if ($source === null) {
                                    continue; // skip keys with no effective value
                                }

                                $rows[] = [
                                    'key' => $vk->key,
                                    'variable_key_id' => $vk->id,
                                    'environment_id' => $envId,
                                    'project_id' => $projectId,
                                    'type' => $vk->type,
                                    'is_secret' => (bool) $vk->is_secret,
                                    'value' => $vk->is_secret ? '••••' : (string) $value,
                                    'raw_value' => (string) $rawValue,
                                    'source' => $source,
                                    'env_value_id' => $envValueId,
                                    'project_value_id' => $projectValueId,
                                ];
                            }

                            return $rows;
                        })
                        ->schema([
                            Infolists\Components\TextEntry::make('key')
                                ->label(__('fields.key'))
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('type')
                                ->label(__('fields.type'))
                                ->badge(),
                            Infolists\Components\TextEntry::make('value')
                                ->label(__('fields.value'))
                                ->suffixActions([
                                    EditAtSourceAction::make(),
                                    AdoptAsProjectDefaultAction::make(),
                                    AdoptAsDefaultAction::make(),
                                ]),
                            Infolists\Components\TextEntry::make('source')
                                ->label(__('fields.source'))
                                ->formatStateUsing(function (string $state): string {
                                    return match ($state) {
                                        'environment' => __('environment.effective_variables.source.environment'),
                                        'project' => __('environment.effective_variables.source.project'),
                                        default => __('environment.effective_variables.source.default'),
                                    };
                                })
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'environment' => 'success',
                                    'project' => 'warning',
                                    default => 'gray',
                                }),
                            Infolists\Components\TextEntry::make('actions')
                                ->state('⋯')
                                ->label('')
                                ->alignment(Alignment::End)
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
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('fields.type'))
                    ->options(array_combine(Environment::TYPES, Environment::TYPES)),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('fields.is_default')),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnvironments::route('/'),
            'create' => Pages\CreateEnvironment::route('/create'),
            'view' => Pages\ViewEnvironment::route('/{record}'),
            'edit' => Pages\EditEnvironment::route('/{record}/edit'),
        ];
    }
}
