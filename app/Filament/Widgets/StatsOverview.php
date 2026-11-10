<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Product;
use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $newInquiries = Inquiry::where('status', 'new')->count();

        return [
            Stat::make('New inquiries', $newInquiries)
                ->description($newInquiries > 0 ? 'Waiting for your reply' : 'All caught up')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color($newInquiries > 0 ? 'warning' : 'success')
                ->chart($this->inquiriesLastSevenDays())
                ->url(InquiryResource::getUrl('index', ['tab' => 'new'])),
            Stat::make('Active products', Product::active()->count())
                ->description(Product::active()->where('is_featured', true)->count().' featured')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->url(ProductResource::getUrl('index')),
            Stat::make('Published posts', Post::published()->count())
                ->description(number_format((int) Post::sum('views')).' total views')
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('info')
                ->url(PostResource::getUrl('index')),
            Stat::make('Subscribers', Subscriber::where('is_active', true)->count())
                ->description('Newsletter')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success')
                ->url(SubscriberResource::getUrl('index')),
        ];
    }

    /** @return array<int, int> inquiry counts per day, oldest first */
    protected function inquiriesLastSevenDays(): array
    {
        $perDay = Inquiry::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->pluck('created_at')
            ->countBy(fn ($date): string => $date->format('Y-m-d'));

        return collect(range(6, 0))
            ->map(fn (int $daysAgo): int => $perDay[now()->subDays($daysAgo)->format('Y-m-d')] ?? 0)
            ->all();
    }
}
