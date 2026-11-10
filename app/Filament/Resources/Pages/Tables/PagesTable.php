<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Support\Fields;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('medium'),
                TextColumn::make('slug')->color('gray')->prefix('/')->searchable(),
                ToggleColumn::make('show_in_footer')->label('In footer'),
                ToggleColumn::make('is_active')->label('Published'),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->defaultSort('title')
            ->recordActions([
                Fields::viewOnSiteAction('pages.show'),
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
