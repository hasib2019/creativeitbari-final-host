<?php

namespace App\Filament\Resources\Disbursements;

use App\Filament\Resources\Disbursements\Pages\ManageDisbursements;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Disbursement;
use App\Models\MerchantApp;
use App\Services\Payments\DisbursementService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Payouts of collected money. Creating one settles the app's unsettled payments. */
class DisbursementResource extends Resource
{
    protected static ?string $model = Disbursement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'reference';

    /** Form for "New disbursement". */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('merchant_app_id')
                    ->label('App')
                    ->options(fn () => MerchantApp::orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $set('account_details', $state ? MerchantApp::find($state)?->payout_details : null);
                    })
                    ->visibleOn('create'),
                DatePicker::make('until')
                    ->label('Include payments paid until')
                    ->helperText('Leave empty to include every unsettled payment.')
                    ->native(false)
                    ->maxDate(now())
                    ->live()
                    ->visibleOn('create'),
                TextEntry::make('preview')
                    ->label('Will be disbursed')
                    ->columnSpanFull()
                    ->size('lg')
                    ->weight('bold')
                    ->color('info')
                    ->state(function (Get $get): string {
                        $app = MerchantApp::find($get('merchant_app_id'));
                        if (! $app) {
                            return 'Choose an app';
                        }

                        $preview = app(DisbursementService::class)->preview($app, $get('until'));

                        return money($preview['amount']).' from '.$preview['count'].' paid payment(s) (net of gateway fee)';
                    })
                    ->visibleOn('create'),
                Select::make('method')->options(Disbursement::METHODS)->required()->native(false)->default('bank'),
                TextInput::make('transaction_reference')->label('Bank / wallet transaction ID')->maxLength(255),
                Textarea::make('account_details')->label('Paid to (account)')->rows(3),
                Textarea::make('note')->rows(3),
                ToggleButtons::make('status')
                    ->options(['completed' => 'Already paid out', 'pending' => 'Pay out later'])
                    ->colors(['completed' => 'success', 'pending' => 'warning'])
                    ->default('completed')
                    ->inline()
                    ->required()
                    ->visibleOn('create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $money = fn ($state): string => money($state ?? 0);

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Created')->dateTime('d M Y')->sortable(),
                TextColumn::make('reference')->searchable()->copyable()->fontFamily('mono'),
                TextColumn::make('merchantApp.name')->label('App')->badge()->color('gray'),
                TextColumn::make('amount')
                    ->formatStateUsing($money)
                    ->alignEnd()
                    ->weight('bold')
                    ->summarize(Sum::make()->label('Total')->formatStateUsing($money)),
                TextColumn::make('payments_count')->label('Payments')->alignEnd(),
                TextColumn::make('method')->formatStateUsing(fn (string $state): string => Disbursement::METHODS[$state] ?? $state),
                TextColumn::make('transaction_reference')->label('Txn ID')->placeholder('—')->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Disbursement::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => Disbursement::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('disbursed_at')->dateTime('d M Y, h:i A')->placeholder('—')->toggleable(),
                TextColumn::make('creator.name')->label('By')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('merchant_app_id')->label('App')->relationship('merchantApp', 'name'),
                SelectFilter::make('status')->options(Disbursement::STATUSES),
            ])
            ->recordActions([
                Action::make('payments')
                    ->label('Payments')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->url(fn (Disbursement $record): string => PaymentResource::getUrl('index', [
                        'filters' => ['disbursement_id' => ['value' => $record->id]],
                    ])),
                ActionGroup::make([
                    Action::make('complete')
                        ->label('Mark paid out')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Disbursement $record): bool => $record->status === 'pending')
                        ->schema([TextInput::make('transaction_reference')->label('Bank / wallet transaction ID')])
                        ->fillForm(fn (Disbursement $record): array => ['transaction_reference' => $record->transaction_reference])
                        ->action(fn (Disbursement $record, array $data) => app(DisbursementService::class)->complete($record, $data['transaction_reference'] ?? null))
                        ->successNotificationTitle('Disbursement completed'),
                    EditAction::make()->label('Edit details'),
                    Action::make('cancel')
                        ->label('Cancel disbursement')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Disbursement $record): bool => $record->status !== 'cancelled')
                        ->requiresConfirmation()
                        ->modalDescription('Its payments become "not disbursed" again and can go into a new disbursement.')
                        ->action(function (Disbursement $record): void {
                            app(DisbursementService::class)->cancel($record);
                            Notification::make()->title('Disbursement cancelled')->success()->send();
                        }),
                ]),
            ]);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDisbursements::route('/'),
        ];
    }
}
