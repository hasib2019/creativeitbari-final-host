<?php

namespace App\Filament\Support;

use App\Support\Icons;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Reusable form/table building blocks shared by the admin resources.
 */
class Fields
{
    /**
     * A title/name input that fills the slug while creating (or when the slug is empty),
     * plus the unique slug input itself.
     *
     * @return array{0: TextInput, 1: TextInput}
     */
    public static function titleWithSlug(string $source = 'title', string $label = 'Title'): array
    {
        return [
            TextInput::make($source)
                ->label($label)
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, ?string $old, string $operation, Get $get, Set $set): void {
                    $currentSlug = (string) $get('slug');

                    if (blank($currentSlug) || ($operation === 'create' && $currentSlug === Str::slug((string) $old))) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->helperText('Used in the page URL. Auto-filled from the '.strtolower($label).'.'),
        ];
    }

    public static function image(string $name, string $directory, ?string $label = null): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->maxSize(4096);
    }

    public static function gallery(string $name, string $directory): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->multiple()
            ->reorderable()
            ->appendFiles()
            ->panelLayout('grid')
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->maxSize(4096)
            ->maxFiles(12);
    }

    public static function richEditor(string $name, string $attachmentsDirectory): RichEditor
    {
        return RichEditor::make($name)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory($attachmentsDirectory)
            ->fileAttachmentsVisibility('public')
            ->columnSpanFull();
    }

    public static function icon(string $name = 'icon'): Select
    {
        return Select::make($name)
            ->options(Icons::options())
            ->searchable()
            ->native(false)
            ->helperText('Heroicon shown on the website.');
    }

    /** A "list of strings" field (features, bullet points…) stored as a plain array. */
    public static function stringList(string $name, string $itemLabel = 'Item'): Repeater
    {
        return Repeater::make($name)
            ->simple(
                TextInput::make('value')->required()->maxLength(255)->placeholder($itemLabel),
            )
            ->reorderableWithButtons()
            ->addActionLabel('Add '.strtolower($itemLabel))
            ->defaultItems(0)
            ->columnSpanFull();
    }

    public static function seoSection(): Section
    {
        return Section::make('SEO')
            ->description('Optional. Falls back to the title and short description.')
            ->icon('heroicon-o-magnifying-glass')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('meta_title')
                    ->maxLength(70)
                    ->helperText('Recommended: 50–60 characters.'),
                Textarea::make('meta_description')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Recommended: 150–160 characters.'),
            ]);
    }

    /** @return array<Component|Field> */
    public static function visibility(bool $featured = false): array
    {
        return array_values(array_filter([
            Toggle::make('is_active')->label('Active (visible on website)')->default(true),
            $featured ? Toggle::make('is_featured')->label('Featured (show on homepage)') : null,
            TextInput::make('sort_order')->numeric()->default(0)->minValue(0)->helperText('Lower numbers show first. You can also drag rows in the list.'),
        ]));
    }

    /**
     * A 500×200 (5:2) brand logo. The editor opens on that ratio so an image of
     * another shape can be cropped to fit instead of being squashed on the site,
     * and anything larger is scaled down to the size the header actually uses.
     */
    public static function logo(string $name, string $label): FileUpload
    {
        return self::image($name, 'settings', $label)
            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
            ->imageEditorAspectRatioOptions(['5:2', null])
            ->automaticallyResizeImagesMode('contain')
            ->automaticallyResizeImagesToWidth('500')
            ->automaticallyResizeImagesToHeight('200')
            ->automaticallyUpscaleImagesWhenResizing(false)
            ->maxSize(2048);
    }

    /**
     * The site icon. Cropped to a square and scaled to 512×512 in the browser
     * before upload (the editor opens on 1:1 to choose the crop); on save,
     * App\Support\Favicon makes the 16/32/48/180/192 px sizes and favicon.ico.
     */
    public static function favicon(string $name, string $label): FileUpload
    {
        return self::image($name, 'settings', $label)
            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
            ->imageAspectRatio('1:1')
            ->automaticallyCropImagesToAspectRatio()
            ->imageEditorAspectRatioOptions(['1:1'])
            ->automaticallyResizeImagesMode('cover')
            ->automaticallyResizeImagesToWidth('512')
            ->automaticallyResizeImagesToHeight('512')
            ->maxSize(2048)
            ->helperText('Any PNG, JPG or WebP — it is cropped to a square and resized to 512×512 automatically (use the pencil to choose the crop). Browser-tab, iPhone and Android sizes and favicon.ico are made from it on save. A transparent PNG looks best.');
    }

    public static function imageColumn(string $name, bool $circular = false): ImageColumn
    {
        $column = ImageColumn::make($name)->disk('public')->label('')->width(56)->height(40);

        return $circular ? $column->circular()->width(40) : $column;
    }

    /**
     * Table row action that opens the record's public page in a new tab.
     * Hidden when the frontend route is not registered.
     */
    public static function viewOnSiteAction(string $routeName): Action
    {
        return Action::make('viewOnSite')
            ->label('View')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->color('gray')
            ->visible(fn (): bool => Route::has($routeName))
            ->url(fn ($record): ?string => Route::has($routeName) ? route($routeName, $record) : null, shouldOpenInNewTab: true);
    }

    public static function currencyPrefix(): string
    {
        return (string) setting('currency_symbol', '৳');
    }
}
