<?php

namespace App\Filament\Resources\HostingPlans;

use App\Filament\Resources\HostingPlans\Pages\ManageHostingPlans;
use App\Filament\Support\Fields;
use App\Models\HostingPlan;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class HostingPlanResource extends Resource
{
    protected static ?string $model = HostingPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                ...Fields::titleWithSlug('name', 'Plan name'),
                Select::make('type')
                    ->options(HostingPlan::TYPES)
                    ->default('shared')
                    ->required()
                    ->native(false),
                TextInput::make('order_url')
                    ->label('Order URL')
                    ->url()
                    ->maxLength(255)
                    ->helperText('Optional external order link (e.g. WHMCS). Leave empty to use the website order form.'),
                Textarea::make('short_description')->rows(2)->maxLength(500)->columnSpanFull(),
                TextInput::make('monthly_price')
                    ->numeric()
                    ->minValue(0)
                    ->prefix(fn (): string => Fields::currencyPrefix()),
                TextInput::make('yearly_price')
                    ->numeric()
                    ->minValue(0)
                    ->prefix(fn (): string => Fields::currencyPrefix()),
                Fields::stringList('features', 'Feature')
                    ->helperText('e.g. 10 GB NVMe SSD, Free SSL, cPanel, Daily backup'),
                Section::make('Display')
                    ->schema([
                        Toggle::make('is_popular')->label('Mark as most popular'),
                        ...Fields::visibility(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => HostingPlan::TYPES[$state] ?? $state),
                TextColumn::make('monthly_price')->label('Monthly')->formatStateUsing(fn ($state): string => money($state))->sortable()->placeholder('—'),
                TextColumn::make('yearly_price')->label('Yearly')->formatStateUsing(fn ($state): string => money($state))->sortable()->placeholder('—'),
                ToggleColumn::make('is_popular')->label('Popular'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('type')->options(HostingPlan::TYPES),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes(['created_at', 'updated_at'])
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['name'] .= ' (copy)';
                        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
                        $data['is_popular'] = false;

                        return $data;
                    }),
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
            'index' => ManageHostingPlans::route('/'),
        ];
    }
}
