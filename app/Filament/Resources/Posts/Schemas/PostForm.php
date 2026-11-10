<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
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
                            Textarea::make('excerpt')
                                ->rows(3)
                                ->maxLength(500)
                                ->helperText('Short summary for blog cards. Auto-generated from content if empty.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('Content')
                        ->schema([
                            Fields::richEditor('content', 'posts/content')->hiddenLabel()->required(),
                        ]),
                    Fields::seoSection(),
                ])->columnSpan(['lg' => 2]),

                Group::make([
                    Section::make('Publishing')
                        ->schema([
                            Toggle::make('is_published')->label('Published')->default(true),
                            Toggle::make('is_featured')->label('Featured'),
                            DateTimePicker::make('published_at')
                                ->label('Publish date')
                                ->default(now())
                                ->native(false)
                                ->helperText('Future date = scheduled post.'),
                            Select::make('user_id')
                                ->label('Author')
                                ->relationship('author', 'name')
                                ->default(fn (): ?int => auth()->id())
                                ->searchable()
                                ->preload(),
                        ]),
                    Section::make('Organize')
                        ->schema([
                            Select::make('blog_category_id')
                                ->label('Category')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    ...Fields::titleWithSlug('name', 'Name'),
                                ]),
                            TagsInput::make('tags')->placeholder('Add tag & press Enter'),
                        ]),
                    Section::make('Featured image')
                        ->schema([
                            Fields::image('thumbnail', 'posts')->hiddenLabel(),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
