<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestInquiries extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest inquiries')
            ->query(fn () => Inquiry::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')->label('Received')->since(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::TYPES[$state] ?? $state)
                    ->color(fn (string $state): string => InquiryResource::TYPE_COLORS[$state] ?? 'gray'),
                TextColumn::make('name')->weight('medium')->description(fn (Inquiry $record): string => $record->email),
                TextColumn::make('item_name')->label('Item')->limit(30)->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => InquiryResource::STATUS_COLORS[$state] ?? 'gray'),
            ])
            ->recordUrl(fn (Inquiry $record): string => InquiryResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('all')
                    ->label('View all')
                    ->link()
                    ->url(InquiryResource::getUrl('index')),
            ])
            ->emptyStateHeading('No inquiries yet')
            ->emptyStateDescription('Contact form messages and orders from the website will appear here.')
            ->emptyStateIcon('heroicon-o-inbox');
    }
}
