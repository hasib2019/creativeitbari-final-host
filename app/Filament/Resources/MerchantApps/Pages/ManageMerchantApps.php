<?php

namespace App\Filament\Resources\MerchantApps\Pages;

use App\Filament\Resources\MerchantApps\MerchantAppResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMerchantApps extends ManageRecords
{
    protected static string $resource = MerchantAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Connect an app')
                ->successNotificationTitle('App connected — open "API keys" to copy its credentials'),
        ];
    }
}
