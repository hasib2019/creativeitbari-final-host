<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\PaymentActions;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        $money = fn ($state): string => money($state ?? 0);

        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->description(fn (Payment $record): string => 'Order '.$record->order_id),
                TextColumn::make('merchantApp.name')
                    ->label('App')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(['customer_name', 'customer_phone', 'customer_email'])
                    ->description(fn (Payment $record): ?string => $record->customer_phone)
                    ->placeholder('—'),
                TextColumn::make('description')->limit(30)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('amount')
                    ->formatStateUsing($money)
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->formatStateUsing($money)),
                TextColumn::make('net_amount')
                    ->label('Net')
                    ->formatStateUsing($money)
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Sum::make()->label('Net')->formatStateUsing($money)),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Payment::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => Payment::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),
                TextColumn::make('payment_method')->label('Method')->placeholder('—')->toggleable(),
                TextColumn::make('paid_at')->dateTime('d M Y, h:i A')->sortable()->placeholder('—')->toggleable(),
                TextColumn::make('disbursement.reference')
                    ->label('Settlement')
                    ->placeholder(fn (Payment $record): string => $record->isPaid() ? 'Unsettled' : '—')
                    ->badge()
                    ->color('success')
                    ->toggleable(),
                TextColumn::make('webhook_status')
                    ->label('Webhook')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Payment $record): string => PaymentResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('status')->options(Payment::STATUSES)->multiple(),
                SelectFilter::make('merchant_app_id')->label('App')->relationship('merchantApp', 'name')->preload(),
                TernaryFilter::make('settled')
                    ->label('Settlement')
                    ->placeholder('All')
                    ->trueLabel('Settled (disbursed)')
                    ->falseLabel('Paid, not settled')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('disbursement_id'),
                        false: fn (Builder $query) => $query->unsettled(),
                    ),
                SelectFilter::make('disbursement_id')->label('Disbursement')->relationship('disbursement', 'reference')->searchable(),
                Filter::make('created_at')
                    ->label('Date')
                    ->schema([
                        DatePicker::make('from')->native(false),
                        DatePicker::make('until')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d)))
                    ->indicateUsing(fn (array $data): array => array_filter([
                        isset($data['from']) ? 'From '.$data['from'] : null,
                        isset($data['until']) ? 'Until '.$data['until'] : null,
                    ])),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    PaymentActions::verify(),
                    PaymentActions::resendWebhook(),
                ]),
            ]);
    }
}
