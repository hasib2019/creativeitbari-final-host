<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTabs(): array
    {
        $counts = Payment::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $unsettled = Payment::unsettled()->count();

        return [
            'all' => Tab::make('All'),
            'paid' => Tab::make('Paid')
                ->badge($counts['paid'] ?? null)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'paid')),
            'unsettled' => Tab::make('Not disbursed')
                ->badge($unsettled ?: null)
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->unsettled()),
            'processing' => Tab::make('Processing')
                ->badge($counts['processing'] ?? null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'processing')),
            'unpaid' => Tab::make('Failed / cancelled')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', ['failed', 'cancelled', 'expired'])),
        ];
    }
}
