<?php

namespace App\Filament\Pages;

use App\Filament\Support\Fields;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use UnitEnum;

/**
 * Key/value site settings (Setting model). The frontend reads these keys via setting('key').
 *
 * @property-read Schema $form
 */
class SiteSettings extends Page
{
    protected string $view = 'filament-panels::pages.page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Site Settings';

    protected static ?string $slug = 'site-settings';

    /** Values used when a setting has never been saved. */
    public const DEFAULTS = [
        'site_name' => 'CreativeItBari',
        'currency_symbol' => '৳',
        'currency_code' => 'BDT',
    ];

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(array_merge(self::DEFAULTS, array_filter(
            Setting::allCached(),
            fn ($value): bool => $value !== null && $value !== '',
        )));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        $this->generalTab(),
                        $this->contactTab(),
                        $this->homepageTab(),
                        $this->aboutTab(),
                        $this->footerTab(),
                        $this->seoTab(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save settings')
                            ->icon('heroicon-o-check')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setMany($data);

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->body('Changes are live on the website.')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label('Clear website cache')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Clears cached settings, views, routes and config. Safe to run any time.')
                ->action(function (): void {
                    Artisan::call('optimize:clear');

                    Notification::make()->success()->title('Cache cleared')->send();
                }),
        ];
    }

    protected function generalTab(): Tab
    {
        return Tab::make('General')
            ->icon('heroicon-o-building-office')
            ->schema([
                Section::make('Brand')
                    ->schema([
                        TextInput::make('site_name')->required()->maxLength(100),
                        TextInput::make('site_tagline')->maxLength(150)->placeholder('e.g. Software, Hosting & Web Solutions'),
                        Fields::logo('logo', 'Logo (light mode)')
                            ->helperText('Shown on light backgrounds. 500×200 px (5:2), transparent PNG/WebP/SVG looks best.'),
                        Fields::logo('logo_dark', 'Logo (dark mode)')
                            ->helperText('White/light version for dark mode and the dark hero. 500×200 px (5:2).'),
                        Fields::image('favicon', 'settings', 'Favicon')
                            ->imageEditor(false)
                            ->maxSize(1024)
                            ->helperText('Square PNG, 512×512px recommended.'),
                    ])
                    ->columns(2),
                Section::make('Currency')
                    ->schema([
                        TextInput::make('currency_symbol')->required()->maxLength(5)->placeholder('৳'),
                        TextInput::make('currency_code')->required()->maxLength(5)->placeholder('BDT'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function contactTab(): Tab
    {
        return Tab::make('Contact & Social')
            ->icon('heroicon-o-phone')
            ->schema([
                Section::make('Contact information')
                    ->schema([
                        TextInput::make('phone')->tel()->maxLength(50),
                        TextInput::make('phone_2')->label('Phone 2')->tel()->maxLength(50),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp number')
                            ->maxLength(20)
                            ->placeholder('8801738356180')
                            ->helperText('With country code, digits only. Used for the floating WhatsApp button.'),
                        Textarea::make('address')->rows(2)->columnSpanFull(),
                        TextInput::make('working_hours')->maxLength(150)->placeholder('Sat – Thu, 10:00 AM – 8:00 PM'),
                        Textarea::make('google_map_embed')
                            ->label('Google Maps embed URL')
                            ->rows(2)
                            ->helperText('Google Maps → Share → Embed a map → copy only the src="…" URL.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Social media')
                    ->schema([
                        TextInput::make('social_facebook')->label('Facebook')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_youtube')->label('YouTube')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_linkedin')->label('LinkedIn')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_instagram')->label('Instagram')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_twitter')->label('X / Twitter')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_github')->label('GitHub')->url()->prefixIcon('heroicon-o-link'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function homepageTab(): Tab
    {
        return Tab::make('Homepage')
            ->icon('heroicon-o-home')
            ->schema([
                Section::make('Hero section')
                    ->schema([
                        TextInput::make('hero_badge')->maxLength(100)->placeholder('e.g. #1 Software Company in Bangladesh'),
                        TextInput::make('hero_highlight')
                            ->maxLength(100)
                            ->helperText('Word(s) from the title shown with a gradient.'),
                        TextInput::make('hero_title')->maxLength(150)->columnSpanFull(),
                        TagsInput::make('hero_typed_words')
                            ->label('Typing words')
                            ->placeholder('Add a word and press Enter')
                            ->helperText('Typed one after another after the title, e.g. SaaS Products, ERP Systems, E-commerce Stores. Empty = the highlight above.')
                            ->columnSpanFull(),
                        Textarea::make('hero_subtitle')->rows(3)->maxLength(500)->columnSpanFull(),
                        Fields::image('hero_image', 'settings', 'Hero image')->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('hero_primary_text')->label('Primary button text')->maxLength(50),
                                TextInput::make('hero_primary_url')->label('Primary button link')->maxLength(255)->placeholder('/products'),
                                TextInput::make('hero_secondary_text')->label('Secondary button text')->maxLength(50),
                                TextInput::make('hero_secondary_url')->label('Secondary button link')->maxLength(255)->placeholder('/contact'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Stats / counters')
                    ->description('e.g. "250+" → "Projects delivered"')
                    ->collapsible()
                    ->schema([
                        Repeater::make('home_stats')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('value')->required()->maxLength(20)->placeholder('250+'),
                                TextInput::make('label')->required()->maxLength(60)->placeholder('Projects delivered'),
                            ])
                            ->columns(2)
                            ->grid(2)
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->maxItems(8)
                            ->addActionLabel('Add stat'),
                    ]),
                Section::make('How we work (process steps)')
                    ->collapsible()
                    ->schema([
                        Repeater::make('home_process')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('title')->required()->maxLength(80),
                                Textarea::make('description')->rows(2)->maxLength(300),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->maxItems(8)
                            ->addActionLabel('Add step'),
                    ]),
                Section::make('Why choose us')
                    ->collapsible()
                    ->schema([
                        Repeater::make('home_why_us')
                            ->hiddenLabel()
                            ->schema([
                                Fields::icon(),
                                TextInput::make('title')->required()->maxLength(80),
                                Textarea::make('description')->rows(2)->maxLength(300)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->maxItems(12)
                            ->addActionLabel('Add reason'),
                    ]),
                Section::make('Call to action banner')
                    ->collapsible()
                    ->schema([
                        TextInput::make('cta_title')->maxLength(150)->columnSpanFull(),
                        Textarea::make('cta_subtitle')->rows(2)->maxLength(300)->columnSpanFull(),
                        TextInput::make('cta_button_text')->maxLength(50),
                        TextInput::make('cta_button_url')->maxLength(255)->placeholder('/contact'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function aboutTab(): Tab
    {
        return Tab::make('About')
            ->icon('heroicon-o-information-circle')
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('about_title')->maxLength(150),
                        TextInput::make('about_experience_years')
                            ->label('Years of experience')
                            ->maxLength(10)
                            ->placeholder('10+'),
                        Fields::richEditor('about_content', 'settings/about'),
                        Fields::image('about_image', 'settings', 'About image')->columnSpanFull(),
                        Textarea::make('about_mission')->label('Our mission')->rows(3),
                        Textarea::make('about_vision')->label('Our vision')->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    protected function footerTab(): Tab
    {
        return Tab::make('Footer')
            ->icon('heroicon-o-bars-3-bottom-left')
            ->schema([
                Section::make()
                    ->schema([
                        Textarea::make('footer_about')->label('Footer about text')->rows(3)->maxLength(500),
                        TextInput::make('copyright_text')
                            ->maxLength(255)
                            ->placeholder('© 2026 CreativeItBari. All rights reserved.'),
                    ]),
            ]);
    }

    protected function seoTab(): Tab
    {
        return Tab::make('SEO & Scripts')
            ->icon('heroicon-o-magnifying-glass')
            ->schema([
                Section::make('Default SEO')
                    ->description('Used on pages without their own SEO values (e.g. the homepage).')
                    ->schema([
                        TextInput::make('seo_meta_title')->label('Meta title')->maxLength(70),
                        TextInput::make('seo_meta_keywords')->label('Meta keywords')->maxLength(255)->placeholder('software company, web hosting, domain, SaaS'),
                        Textarea::make('seo_meta_description')->label('Meta description')->rows(3)->maxLength(500)->columnSpanFull(),
                        Fields::image('seo_og_image', 'settings', 'Social share image (Open Graph)')
                            ->helperText('1200×630px. Shown when links are shared on Facebook, LinkedIn, etc.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Tracking & verification')
                    ->schema([
                        TextInput::make('google_site_verification')
                            ->label('Google Search Console verification code')
                            ->helperText('Only the content="…" value.'),
                        TextInput::make('google_analytics_id')->label('Google Analytics ID')->placeholder('G-XXXXXXXXXX'),
                        TextInput::make('facebook_pixel_id')->label('Facebook Pixel ID'),
                    ])
                    ->columns(3),
                Section::make('Custom code')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('custom_head_code')
                            ->label('Custom <head> code')
                            ->rows(5)
                            ->extraInputAttributes(['class' => 'font-mono text-xs'])
                            ->helperText('Injected before </head> on every page (e.g. chat widgets, extra meta tags).'),
                        Textarea::make('custom_body_code')
                            ->label('Custom <body> code')
                            ->rows(5)
                            ->extraInputAttributes(['class' => 'font-mono text-xs'])
                            ->helperText('Injected before </body> on every page.'),
                    ]),
            ]);
    }
}
