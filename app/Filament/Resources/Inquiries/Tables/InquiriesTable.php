<?php

namespace App\Filament\Resources\Inquiries\Tables;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class InquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y, h:i A')
                    ->description(fn (Inquiry $record): string => $record->created_at->diffForHumans())
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::TYPES[$state] ?? $state)
                    ->color(fn (string $state): string => InquiryResource::TYPE_COLORS[$state] ?? 'gray'),
                TextColumn::make('name')
                    ->searchable()
                    ->weight(fn (Inquiry $record): string => $record->status === 'new' ? 'bold' : 'normal')
                    ->description(fn (Inquiry $record): ?string => $record->company),
                TextColumn::make('email')->searchable()->copyable()->toggleable(),
                TextColumn::make('phone')->searchable()->copyable()->toggleable(),
                TextColumn::make('item_name')->label('Item')->searchable()->limit(30)->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Inquiry::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => InquiryResource::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Inquiry $record): string => InquiryResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('type')->options(Inquiry::TYPES),
                SelectFilter::make('status')->options(Inquiry::STATUSES),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    Action::make('setStatus')
                        ->label('Change status')
                        ->icon('heroicon-o-arrow-path')
                        ->fillForm(fn (Inquiry $record): array => ['status' => $record->status])
                        ->schema([
                            Select::make('status')->options(Inquiry::STATUSES)->required()->native(false),
                        ])
                        ->action(fn (Inquiry $record, array $data) => $record->update(['status' => $data['status']]))
                        ->successNotificationTitle('Status updated'),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markContacted')
                        ->label('Mark as contacted')
                        ->icon('heroicon-o-check')
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'contacted']))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
