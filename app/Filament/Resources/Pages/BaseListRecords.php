<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

abstract class BaseListRecords extends ListRecords
{
    protected function getHeaderActions(): array
    {
        // Standard: Show Create button at top-right of list
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        // Standard: Row click opens the View page; no inline edit on row click.
        $table = parent::table($table);

        return $table
            ->recordUrl(function ($record): string {
                /** @var class-string<\Filament\Resources\Resource> $resource */
                $resource = static::getResource();

                return $resource::getUrl('view', ['record' => $record]);
            })
            ->recordAction(null);
    }
}
