<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Filament\Support\Fields;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('designation')->maxLength(255)->placeholder('e.g. CEO'),
                TextInput::make('company')->maxLength(255),
                Select::make('rating')
                    ->options([5 => '★★★★★ (5)', 4 => '★★★★ (4)', 3 => '★★★ (3)', 2 => '★★ (2)', 1 => '★ (1)'])
                    ->default(5)
                    ->required()
                    ->native(false),
                Textarea::make('content')->label('Review')->required()->rows(4)->columnSpanFull(),
                Fields::image('photo', 'testimonials')->avatar()->columnSpanFull(),
                ...Fields::visibility(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Fields::imageColumn('photo', circular: true),
                TextColumn::make('name')->searchable()->sortable()->weight('medium')
                    ->description(fn (Testimonial $record): ?string => collect([$record->designation, $record->company])->filter()->implode(', ') ?: null),
                TextColumn::make('content')->label('Review')->limit(70)->wrap()->toggleable(),
                TextColumn::make('rating')->formatStateUsing(fn ($state): string => str_repeat('★', (int) $state))->color('warning')->sortable(),
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
            'index' => ManageTestimonials::route('/'),
        ];
    }
}
