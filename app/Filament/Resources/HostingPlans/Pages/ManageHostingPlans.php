<?php

namespace App\Filament\Resources\HostingPlans\Pages;

use App\Filament\Resources\HostingPlans\HostingPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHostingPlans extends ManageRecords
{
    protected static string $resource = HostingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
