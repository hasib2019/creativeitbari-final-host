<?php

namespace App\Filament\Resources\Portfolios\Schemas;

use App\Filament\Support\Fields;
use App\Models\Portfolio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PortfolioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Project details')
                        ->schema([
                            ...Fields::titleWithSlug('title', 'Title'),
                            TextInput::make('category')
                                ->maxLength(255)
                                ->placeholder('e.g. E-commerce, SaaS, Corporate Website')
                                ->datalist(fn (): array => Portfolio::query()
                                    ->whereNotNull('category')
                                    ->distinct()
                                    ->orderBy('category')
                                    ->pluck('category')
                                    ->all()),
                            TextInput::make('client_name')->maxLength(255),
                            TextInput::make('project_url')->label('Live URL')->url()->maxLength(255),
                            DatePicker::make('completed_at')->label('Completed on')->native(false),
                            Textarea::make('short_description')->rows(3)->maxLength(500)->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('Case study')
                        ->schema([
                            Fields::richEditor('content', 'portfolios/content')->hiddenLabel(),
                        ]),
                    Fields::seoSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Media')
                        ->schema([
                            Fields::image('thumbnail', 'portfolios'),
                            Fields::gallery('gallery', 'portfolios/gallery'),
                        ]),
                    Section::make('Technologies')
                        ->schema([
                            TagsInput::make('technologies')->hiddenLabel()->placeholder('Laravel, React… (press Enter)'),
                        ]),
                    Section::make('Visibility')
                        ->schema(Fields::visibility(featured: true)),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
