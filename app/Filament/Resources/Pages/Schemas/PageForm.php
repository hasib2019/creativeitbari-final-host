<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make()
                        ->schema([
                            ...Fields::titleWithSlug('title', 'Title'),
                        ])
                        ->columns(2),
                    Section::make('Content')
                        ->schema([
                            Fields::richEditor('content', 'pages/content')->hiddenLabel(),
                        ]),
                    Fields::seoSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Visibility')
                        ->schema([
                            Toggle::make('is_active')->label('Published')->default(true),
                            Toggle::make('show_in_footer')->label('Show link in footer')->default(true),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
