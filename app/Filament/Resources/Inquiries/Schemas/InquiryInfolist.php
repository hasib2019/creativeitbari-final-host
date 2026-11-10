<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Inquiry details')
                    ->schema([
                        TextEntry::make('name')->weight('bold'),
                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->url(fn (Inquiry $record): string => 'mailto:'.$record->email),
                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->copyable()
                            ->url(fn (Inquiry $record): ?string => $record->phone ? 'tel:'.$record->phone : null)
                            ->placeholder('—'),
                        TextEntry::make('company')->placeholder('—'),
                        TextEntry::make('item_name')->label('Product / Item')->placeholder('—'),
                        TextEntry::make('budget')->placeholder('—'),
                        TextEntry::make('subject')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('message')
                            ->placeholder('—')
                            ->prose()
                            ->formatStateUsing(fn (?string $state): string => nl2br(e((string) $state)))
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),
                Section::make('Status')
                    ->schema([
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Inquiry::TYPES[$state] ?? $state)
                            ->color(fn (string $state): string => InquiryResource::TYPE_COLORS[$state] ?? 'gray'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Inquiry::STATUSES[$state] ?? $state)
                            ->color(fn (string $state): string => InquiryResource::STATUS_COLORS[$state] ?? 'gray'),
                        TextEntry::make('admin_note')->label('Internal note')->placeholder('No note yet'),
                        TextEntry::make('created_at')->label('Received')->dateTime('d M Y, h:i A')->since(),
                        TextEntry::make('ip_address')->label('IP address')->placeholder('—')->color('gray'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }
}
