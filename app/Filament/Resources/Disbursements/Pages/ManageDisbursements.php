<?php

namespace App\Filament\Resources\Disbursements\Pages;

use App\Filament\Resources\Disbursements\DisbursementResource;
use App\Models\MerchantApp;
use App\Services\Payments\DisbursementService;
use App\Services\Payments\PaymentException;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageDisbursements extends ManageRecords
{
    protected static string $resource = DisbursementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New disbursement')
                ->modalWidth('2xl')
                ->using(function (array $data, Action $action): Model {
                    try {
                        return app(DisbursementService::class)->create(
                            MerchantApp::findOrFail($data['merchant_app_id']),
                            $data,
                            auth()->id(),
                        );
                    } catch (PaymentException $e) {
                        Notification::make()->title($e->getMessage())->danger()->send();
                        $action->halt();
                    }
                })
                ->successNotificationTitle('Disbursement recorded'),
        ];
    }
}
