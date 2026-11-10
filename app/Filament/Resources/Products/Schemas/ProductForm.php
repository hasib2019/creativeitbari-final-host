<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\Fields;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public const PLAN_PERIODS = [
        'monthly' => 'Per month',
        'yearly' => 'Per year',
        'one_time' => 'One-time',
        'lifetime' => 'Lifetime',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Product details')
                        ->schema([
                            ...Fields::titleWithSlug('name', 'Name'),
                            Select::make('type')
                                ->options(Product::TYPES)
                                ->required()
                                ->native(false)
                                ->default('saas'),
                            Select::make('product_category_id')
                                ->label('Category')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    ...Fields::titleWithSlug('name', 'Name'),
                                    Fields::icon(),
                                ]),
                            TextInput::make('tagline')
                                ->maxLength(255)
                                ->placeholder('e.g. All-in-one school management software')
                                ->columnSpanFull(),
                            Textarea::make('short_description')
                                ->rows(3)
                                ->maxLength(500)
                                ->helperText('Shown on product cards and used as the default meta description.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Section::make('Description')
                        ->schema([
                            Fields::richEditor('description', 'products/content')->hiddenLabel(),
                        ]),

                    Section::make('Features & technologies')
                        ->schema([
                            Fields::stringList('features', 'Feature'),
                            TagsInput::make('technologies')
                                ->placeholder('Laravel, Vue, MySQL… (press Enter)')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Pricing plans')
                        ->description('Optional packages (e.g. Basic / Standard / Premium). Leave empty to show only the single price.')
                        ->collapsible()
                        ->schema([
                            Repeater::make('plans')
                                ->hiddenLabel()
                                ->schema([
                                    TextInput::make('name')->required()->maxLength(100),
                                    TextInput::make('price')
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix(fn (): string => Fields::currencyPrefix()),
                                    Select::make('period')
                                        ->options(self::PLAN_PERIODS)
                                        ->default('monthly')
                                        ->required()
                                        ->native(false),
                                    Toggle::make('is_popular')
                                        ->label('Highlight as popular')
                                        ->inline(false),
                                    TagsInput::make('features')
                                        ->placeholder('Add a feature and press Enter')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->reorderableWithButtons()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                ->addActionLabel('Add plan')
                                ->defaultItems(0),
                        ]),

                    Fields::seoSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Pricing')
                        ->schema([
                            TextInput::make('price')
                                ->label('Regular price')
                                ->numeric()
                                ->minValue(0)
                                ->prefix(fn (): string => Fields::currencyPrefix()),
                            TextInput::make('sale_price')
                                ->label('Sale price')
                                ->numeric()
                                ->minValue(0)
                                ->prefix(fn (): string => Fields::currencyPrefix())
                                ->helperText('Optional discounted price.'),
                            TextInput::make('price_label')
                                ->maxLength(100)
                                ->placeholder('e.g. Starting from / per month'),
                        ]),

                    Section::make('Media')
                        ->schema([
                            Fields::image('thumbnail', 'products'),
                            Fields::gallery('gallery', 'products/gallery'),
                        ]),

                    Section::make('Links')
                        ->schema([
                            TextInput::make('demo_url')->label('Live demo URL')->url()->maxLength(255),
                            TextInput::make('video_url')->label('Video URL (YouTube)')->url()->maxLength(255),
                        ]),

                    Section::make('Visibility')
                        ->schema(Fields::visibility(featured: true)),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
