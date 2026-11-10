<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Service details')
                        ->schema([
                            ...Fields::titleWithSlug('title', 'Title'),
                            Textarea::make('short_description')
                                ->rows(3)
                                ->maxLength(500)
                                ->helperText('Shown on service cards.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('Content')
                        ->schema([
                            Fields::richEditor('content', 'services/content')->hiddenLabel(),
                        ]),
                    Section::make('Key features')
                        ->schema([
                            Fields::stringList('features', 'Feature')->hiddenLabel(),
                        ]),
                    Fields::seoSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Appearance')
                        ->schema([
                            Fields::icon(),
                            Fields::image('image', 'services', 'Cover image'),
                        ]),
                    Section::make('Visibility')
                        ->schema(Fields::visibility(featured: true)),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
