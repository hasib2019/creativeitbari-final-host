<?php

namespace App\Filament\Resources\MerchantApps;

use App\Filament\Resources\MerchantApps\Pages\ManageMerchantApps;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\MerchantApp;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Apps allowed to take payments through the hub (ERP, client sites …). */
class MerchantAppResource extends Resource
{
    protected static ?string $model = MerchantApp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Connected Apps';

    protected static ?string $modelLabel = 'connected app';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->maxLength(255)->placeholder('Optical ERP'),
                TextInput::make('contact_email')->email()->maxLength(255),
                TextInput::make('webhook_url')
                    ->label('Webhook URL')
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://erp.example.com/webhook/central-pay')
                    ->helperText('Server-to-server POST on every status change. A payment can override it.')
                    ->columnSpanFull(),
                TagsInput::make('allowed_hosts')
                    ->label('Allowed hosts')
                    ->placeholder('erp.example.com')
                    ->helperText('Hosts the app may use for return / cancel / webhook URLs, e.g. erp.example.com, *.example.com, 127.0.0.1:8001. Empty = any host.')
                    ->columnSpanFull(),
                TextInput::make('fee_percent')
                    ->label('Gateway fee (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->default(0)
                    ->suffix('%')
                    ->helperText('Deducted from each payment for the net / disbursable amount.'),
                Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                Textarea::make('payout_details')
                    ->label('Payout account')
                    ->rows(3)
                    ->placeholder("Bank: …\nA/C name: …\nA/C no: …")
                    ->helperText('Pre-filled on new disbursements.'),
                Textarea::make('notes')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        $money = fn ($state): string => money($state ?? 0);

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount(['payments as paid_count' => fn ($p) => $p->paid()])
                ->withSum(['payments as paid_total' => fn ($p) => $p->paid()], 'amount')
                ->withSum(['payments as unsettled_total' => fn ($p) => $p->unsettled()], 'net_amount'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (MerchantApp $record): string => $record->app_key),
                TextColumn::make('paid_count')->label('Paid')->numeric()->alignEnd(),
                TextColumn::make('paid_total')->label('Collected')->formatStateUsing($money)->alignEnd(),
                TextColumn::make('unsettled_total')->label('To disburse')->formatStateUsing($money)->alignEnd()->color('info')->weight('bold'),
                TextColumn::make('fee_percent')->label('Fee')->suffix('%')->toggleable(),
                TextColumn::make('last_used_at')->label('Last API call')->since()->placeholder('Never')->toggleable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                Action::make('credentials')
                    ->label('API keys')
                    ->icon('heroicon-o-key')
                    ->color('primary')
                    ->modalHeading(fn (MerchantApp $record): string => 'API keys — '.$record->name)
                    ->modalDescription('Put these in the app\'s .env. Keep the secret private: anyone with it can create payments for this app.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema([
                        TextEntry::make('api_url')->label('CENTRAL_PAY_URL')->state(url('/'))->copyable()->fontFamily('mono'),
                        TextEntry::make('app_key')->label('CENTRAL_PAY_APP_KEY')->state(fn (MerchantApp $record): string => $record->app_key)->copyable()->fontFamily('mono'),
                        TextEntry::make('app_secret')->label('CENTRAL_PAY_APP_SECRET')->state(fn (MerchantApp $record): string => $record->app_secret)->copyable()->fontFamily('mono'),
                    ]),
                ActionGroup::make([
                    Action::make('payments')
                        ->label('View payments')
                        ->icon('heroicon-o-banknotes')
                        ->url(fn (MerchantApp $record): string => PaymentResource::getUrl('index', [
                            'filters' => ['merchant_app_id' => ['value' => $record->id]],
                        ])),
                    EditAction::make(),
                    Action::make('regenerateSecret')
                        ->label('New secret')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('The old secret stops working immediately. Update the app\'s .env right after.')
                        ->action(function (MerchantApp $record): void {
                            $record->update(['app_secret' => MerchantApp::newSecret()]);

                            Notification::make()->title('New secret created')->body('Open "API keys" to copy it.')->warning()->send();
                        }),
                    DeleteAction::make()
                        ->hidden(fn (MerchantApp $record): bool => $record->payments()->exists()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMerchantApps::route('/'),
        ];
    }
}
