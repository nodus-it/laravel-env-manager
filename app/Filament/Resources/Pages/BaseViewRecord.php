<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecord extends ViewRecord
{
    protected function getHeaderActions(): array
    {
        // Standard: Show Edit button in the top-right of the detail view
        return [
            Actions\EditAction::make(),
        ];
    }
}
