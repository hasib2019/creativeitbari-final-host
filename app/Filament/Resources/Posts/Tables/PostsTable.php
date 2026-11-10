<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Filament\Support\Fields;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['author:id,name', 'category:id,name']))
            ->columns([
                Fields::imageColumn('thumbnail'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap()
                    ->description(fn (Post $record): ?string => $record->author?->name),
                TextColumn::make('category.name')->label('Category')->badge()->color('gray')->toggleable(),
                TextColumn::make('status')
                    ->state(fn (Post $record): string => match (true) {
                        ! $record->is_published => 'Draft',
                        $record->published_at?->isFuture() => 'Scheduled',
                        default => 'Published',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Published' => 'success',
                        'Scheduled' => 'info',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('published_at')->label('Publish date')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('views')->numeric()->sortable()->toggleable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('blog_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload(),
                TernaryFilter::make('is_published')->label('Published'),
                TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->recordActions([
                Fields::viewOnSiteAction('blog.show'),
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
