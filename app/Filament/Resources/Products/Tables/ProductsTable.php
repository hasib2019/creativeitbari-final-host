<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Support\Fields;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Fields::imageColumn('thumbnail'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Product $record): ?string => $record->tagline)
                    ->wrap(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Product::TYPES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'saas' => 'info',
                        'one_time' => 'success',
                        'ecommerce' => 'warning',
                        'web_app' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->toggleable(),
                TextColumn::make('price')
                    ->sortable()
                    ->state(fn (Product $record): ?float => $record->effectivePrice())
                    ->formatStateUsing(fn ($state): string => money($state))
                    ->description(fn (Product $record): ?string => $record->hasDiscount() ? 'Was '.money($record->price) : null)
                    ->placeholder('—'),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_active')->label('Active'),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('type')->options(Product::TYPES),
                SelectFilter::make('product_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload(),
                TernaryFilter::make('is_active')->label('Active'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                Fields::viewOnSiteAction('products.show'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
