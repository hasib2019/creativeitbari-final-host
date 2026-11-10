<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInquiries extends ListRecords
{
    protected static string $resource = InquiryResource::class;

    public function getTabs(): array
    {
        $counts = Inquiry::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $tabs = ['all' => Tab::make('All')->badge($counts->sum() ?: null)];

        foreach (['new', 'contacted', 'in_progress', 'completed'] as $status) {
            $tabs[$status] = Tab::make(Inquiry::STATUSES[$status])
                ->badge($counts[$status] ?? null)
                ->badgeColor(InquiryResource::STATUS_COLORS[$status])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        return $tabs;
    }
}
