<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageSubscribers extends ManageRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Download CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['email', 'subscribed', 'joined_at']);

                    Subscriber::query()->orderBy('id')->each(function (Subscriber $subscriber) use ($out): void {
                        fputcsv($out, [
                            $subscriber->email,
                            $subscriber->is_active ? 'yes' : 'no',
                            $subscriber->created_at?->toDateTimeString(),
                        ]);
                    });

                    fclose($out);
                }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv'])),
            CreateAction::make(),
        ];
    }
}
