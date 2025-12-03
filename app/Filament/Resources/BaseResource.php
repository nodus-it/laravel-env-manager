<?php

namespace App\Filament\Resources;

use Filament\Actions;
use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    public static function dateTimeFormat(): string
    {
        return 'd.m.Y H:i';
    }

    /**
     * Standard record actions for tables (ActionGroup with Edit).
     *
     * @return array<int, \Filament\Actions\Action|\Filament\Actions\ActionGroup>
     */
    public static function defaultRecordActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make(),
            ])->label(__('actions.group')),
        ];
    }

    /**
     * Standard toolbar actions for tables (Bulk Delete).
     *
     * @return array<int, \Filament\Actions\Action|\Filament\Actions\BulkActionGroup>
     */
    public static function defaultToolbarActions(): array
    {
        return [
            Actions\BulkActionGroup::make([
                Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
