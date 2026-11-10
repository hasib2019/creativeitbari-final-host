<?php

namespace App\Filament\Resources\DomainPrices\Pages;

use App\Filament\Resources\DomainPrices\DomainPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDomainPrices extends ManageRecords
{
    protected static string $resource = DomainPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
