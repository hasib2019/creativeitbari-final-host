<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Payment;
use App\Models\PaymentAttempt;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $money = fn ($state): string => money($state ?? 0);
        $json = fn ($state): string => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '—';

        return $schema
            ->columns(3)
            ->components([
                Section::make('Payment')
                    ->columns(3)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextEntry::make('reference')->copyable()->fontFamily('mono')->weight('bold'),
                        TextEntry::make('order_id')->label('App order ID')->copyable()->fontFamily('mono'),
                        TextEntry::make('merchantApp.name')->label('App')->badge()->color('gray'),
                        TextEntry::make('description')->columnSpanFull(),
                        TextEntry::make('amount')->formatStateUsing($money)->weight('bold')->size('lg'),
                        TextEntry::make('fee_amount')->label('Gateway fee')->formatStateUsing($money),
                        TextEntry::make('net_amount')->label('Net')->formatStateUsing($money),
                        TextEntry::make('payment_method')->label('Paid with')->placeholder('—'),
                        TextEntry::make('paid_at')->dateTime('d M Y, h:i:s A')->placeholder('—'),
                        TextEntry::make('disbursement.reference')->label('Settled in')->placeholder('Not settled')->badge()->color('success'),
                        TextEntry::make('failure_reason')->placeholder('—')->color('danger')->columnSpanFull()
                            ->visible(fn (Payment $record): bool => filled($record->failure_reason)),
                    ]),
                Section::make('Status')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Payment::STATUSES[$state] ?? $state)
                            ->color(fn (string $state): string => Payment::STATUS_COLORS[$state] ?? 'gray'),
                        TextEntry::make('created_at')->dateTime('d M Y, h:i:s A'),
                        TextEntry::make('expires_at')->label('Checkout link expires')->dateTime('d M Y, h:i A')->placeholder('—'),
                        TextEntry::make('checkout_url')
                            ->label('Checkout link')
                            ->state(fn (Payment $record): string => $record->checkoutUrl())
                            ->url(fn (Payment $record): string => $record->checkoutUrl(), shouldOpenInNewTab: true)
                            ->copyable()
                            ->limit(40),
                        TextEntry::make('ip_address')->label('Created from IP')->color('gray')->placeholder('—'),
                    ]),
                Section::make('Customer')
                    ->columns(3)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextEntry::make('customer_name')->label('Name')->placeholder('—'),
                        TextEntry::make('customer_email')->label('Email')->copyable()->placeholder('—'),
                        TextEntry::make('customer_phone')->label('Phone')->copyable()->placeholder('—'),
                        TextEntry::make('customer_address')->label('Address')->placeholder('—')->columnSpan(2),
                        TextEntry::make('customer_city')->label('City')->placeholder('—'),
                        TextEntry::make('customer_state')->label('District / State')->placeholder('—'),
                        TextEntry::make('customer_postcode')->label('Post code')->placeholder('—'),
                        TextEntry::make('customer_country')->label('Country')->placeholder('—'),
                    ]),
                Section::make('EPS')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextEntry::make('merchant_transaction_id')->label('Merchant transaction ID')->copyable()->fontFamily('mono')->placeholder('—'),
                        TextEntry::make('gateway_transaction_id')->label('EPS transaction ID')->copyable()->fontFamily('mono')->placeholder('—'),
                    ]),
                Section::make('App callback')
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        TextEntry::make('return_url')->limit(80)->copyable(),
                        TextEntry::make('cancel_url')->limit(80)->placeholder('Same as return URL'),
                        TextEntry::make('webhook_url')->limit(80)->placeholder(fn (Payment $record): string => $record->merchantApp?->webhook_url ?: 'None'),
                        TextEntry::make('webhook_status')
                            ->badge()
                            ->formatStateUsing(fn (string $state, Payment $record): string => $state.($record->webhook_attempts ? " · {$record->webhook_attempts} attempt(s)" : '')),
                        TextEntry::make('webhook_last_attempt_at')->dateTime('d M Y, h:i:s A')->placeholder('—'),
                        TextEntry::make('webhook_next_attempt_at')->label('Next retry')->dateTime('d M Y, h:i A')->placeholder('—'),
                        TextEntry::make('webhook_last_response')->label('Last response')->fontFamily('mono')->placeholder('—')->columnSpanFull(),
                        KeyValueEntry::make('metadata')->label('Metadata sent by the app')->columnSpanFull()
                            ->state(fn (Payment $record): array => collect($record->metadata ?? [])->map(fn ($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v))->all()),
                    ]),
                Section::make('Attempts at EPS')
                    ->description('One row per time the customer was sent to EPS.')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('attempts')
                            ->hiddenLabel()
                            ->placeholder('The customer has not gone to EPS yet.')
                            ->columns(4)
                            ->schema([
                                TextEntry::make('merchant_transaction_id')->label('Merchant txn ID')->fontFamily('mono')->copyable(),
                                TextEntry::make('status')->badge()->color(fn (string $state): string => PaymentAttempt::STATUS_COLORS[$state] ?? 'gray'),
                                TextEntry::make('gateway_status')->label('EPS status')->placeholder('—'),
                                TextEntry::make('return_result')->label('Came back via')->placeholder('—'),
                                TextEntry::make('created_at')->label('Started')->dateTime('d M Y, h:i:s A'),
                                TextEntry::make('verified_at')->label('Last checked')->dateTime('d M Y, h:i:s A')->placeholder('—'),
                                TextEntry::make('gateway_transaction_id')->label('EPS txn ID')->fontFamily('mono')->placeholder('—')->columnSpan(2),
                                TextEntry::make('init_response')->label('Initialize response')->formatStateUsing($json)->fontFamily('mono')->size('xs')->columnSpan(2)
                                    ->extraAttributes(['class' => 'whitespace-pre-wrap break-all']),
                                TextEntry::make('verify_response')->label('Status response')->formatStateUsing($json)->fontFamily('mono')->size('xs')->columnSpan(2)
                                    ->extraAttributes(['class' => 'whitespace-pre-wrap break-all']),
                            ]),
                    ]),
            ]);
    }
}
