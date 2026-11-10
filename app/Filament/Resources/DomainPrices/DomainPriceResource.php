<?php

namespace App\Filament\Resources\DomainPrices;

use App\Filament\Resources\DomainPrices\Pages\ManageDomainPrices;
use App\Filament\Support\Fields;
use App\Models\DomainPrice;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class DomainPriceResource extends Resource
{
    protected static ?string $model = DomainPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Domain Prices';

    protected static ?string $modelLabel = 'domain price';

    protected static ?string $recordTitleAttribute = 'tld';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('tld')
                    ->label('Extension (TLD)')
                    ->placeholder('.com')
                    ->required()
                    ->maxLength(30)
                    ->regex('/^\.?[a-zA-Z0-9.-]+$/')
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn (?string $state): string => '.'.ltrim(strtolower(trim((string) $state)), '.'))
                    ->columnSpanFull(),
                TextInput::make('register_price')
                    ->label('Register (1st year)')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->prefix(fn (): string => Fields::currencyPrefix()),
                TextInput::make('renew_price')
                    ->label('Renew')
                    ->numeric()
                    ->minValue(0)
                    ->prefix(fn (): string => Fields::currencyPrefix()),
                TextInput::make('transfer_price')
                    ->label('Transfer')
                    ->numeric()
                    ->minValue(0)
                    ->prefix(fn (): string => Fields::currencyPrefix()),
                Toggle::make('is_popular')->label('Popular (highlight)')->inline(false),
                ...Fields::visibility(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tld')
            ->columns([
                TextColumn::make('tld')->label('TLD')->searchable()->sortable()->weight('bold'),
                TextColumn::make('register_price')->label('Register')->formatStateUsing(fn ($state): string => money($state))->sortable(),
                TextColumn::make('renew_price')->label('Renew')->formatStateUsing(fn ($state): string => money($state))->sortable()->placeholder('—'),
                TextColumn::make('transfer_price')->label('Transfer')->formatStateUsing(fn ($state): string => money($state))->sortable()->placeholder('—'),
                ToggleColumn::make('is_popular')->label('Popular'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDomainPrices::route('/'),
        ];
    }
}
