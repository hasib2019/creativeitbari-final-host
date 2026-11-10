<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Disbursements\DisbursementResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStats extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Payments';

    protected function getStats(): array
    {
        $today = Payment::paid()->where('paid_at', '>=', today());
        $month = Payment::paid()->where('paid_at', '>=', now()->startOfMonth());
        $unsettled = Payment::unsettled();
        $processing = Payment::where('status', 'processing')->count();

        return [
            Stat::make('Collected today', money((float) (clone $today)->sum('amount')))
                ->description((clone $today)->count().' payment(s)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->lastSevenDays())
                ->url(PaymentResource::getUrl('index', ['tab' => 'paid'])),
            Stat::make('This month', money((float) (clone $month)->sum('amount')))
                ->description((clone $month)->count().' payment(s)')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
            Stat::make('To disburse', money((float) (clone $unsettled)->sum('net_amount')))
                ->description((clone $unsettled)->count().' paid payment(s) not settled')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('info')
                ->url(DisbursementResource::getUrl('index')),
            Stat::make('Waiting for EPS', $processing)
                ->description($processing ? 'Being confirmed' : 'Nothing pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color($processing ? 'warning' : 'gray')
                ->url(PaymentResource::getUrl('index', ['tab' => 'processing'])),
        ];
    }

    /** @return array<int, float> */
    protected function lastSevenDays(): array
    {
        $perDay = Payment::paid()
            ->where('paid_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['paid_at', 'amount'])
            ->groupBy(fn (Payment $p): string => $p->paid_at->format('Y-m-d'))
            ->map(fn ($rows) => (float) $rows->sum('amount'));

        return collect(range(6, 0))
            ->map(fn (int $daysAgo): float => $perDay[now()->subDays($daysAgo)->format('Y-m-d')] ?? 0)
            ->all();
    }
}
