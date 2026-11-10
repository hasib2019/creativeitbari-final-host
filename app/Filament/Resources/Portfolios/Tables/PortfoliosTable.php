<?php

namespace App\Filament\Resources\Portfolios\Tables;

use App\Filament\Support\Fields;
use App\Models\Portfolio;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PortfoliosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Fields::imageColumn('thumbnail'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Portfolio $record): ?string => $record->client_name),
                TextColumn::make('category')->badge()->color('gray')->searchable()->sortable(),
                TextColumn::make('completed_at')->label('Completed')->date()->sortable()->toggleable(),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category')
                    ->options(fn (): array => Portfolio::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                Fields::viewOnSiteAction('portfolio.show'),
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
