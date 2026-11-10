<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use UnitEnum;

/** Shows docs/payment-gateway-integration.md inside the admin panel. */
class PaymentIntegrationGuide extends Page
{
    protected string $view = 'filament.pages.payment-integration-guide';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Payments';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Integration Guide';

    protected static ?string $title = 'Payment Gateway — Integration Guide';

    public function getGuideHtml(): string
    {
        $path = base_path('docs/payment-gateway-integration.md');

        if (! File::exists($path)) {
            return '<p>docs/payment-gateway-integration.md is missing.</p>';
        }

        $markdown = str_replace('{{CENTRAL_URL}}', rtrim(url('/'), '/'), File::get($path));

        return Str::markdown($markdown, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
    }
}
