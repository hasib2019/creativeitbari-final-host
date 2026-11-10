<?php

namespace App\Support;

/**
 * Curated Heroicons (outline) offered in admin icon pickers.
 * Stored value is the Blade Icons name, e.g. "heroicon-o-code-bracket",
 * rendered on the frontend with svg($name) / <x-dynamic-component>.
 */
class Icons
{
    public const LIST = [
        'code-bracket' => 'Code',
        'code-bracket-square' => 'Code (square)',
        'command-line' => 'Terminal',
        'computer-desktop' => 'Desktop',
        'device-phone-mobile' => 'Mobile',
        'device-tablet' => 'Tablet',
        'globe-alt' => 'Globe / Web',
        'server' => 'Server',
        'server-stack' => 'Server stack',
        'cloud' => 'Cloud',
        'cloud-arrow-up' => 'Cloud upload',
        'cpu-chip' => 'CPU chip',
        'circle-stack' => 'Database',
        'shopping-cart' => 'Shopping cart',
        'shopping-bag' => 'Shopping bag',
        'building-storefront' => 'Storefront',
        'credit-card' => 'Credit card',
        'banknotes' => 'Money',
        'chart-bar' => 'Bar chart',
        'chart-pie' => 'Pie chart',
        'presentation-chart-line' => 'Analytics',
        'megaphone' => 'Marketing',
        'magnifying-glass' => 'Search / SEO',
        'paint-brush' => 'Design',
        'swatch' => 'Branding',
        'sparkles' => 'Sparkles',
        'rocket-launch' => 'Rocket',
        'light-bulb' => 'Idea',
        'puzzle-piece' => 'Integration',
        'cog-6-tooth' => 'Settings',
        'wrench-screwdriver' => 'Maintenance',
        'shield-check' => 'Security',
        'lock-closed' => 'Lock',
        'key' => 'Key',
        'bolt' => 'Fast / Power',
        'clock' => 'Time',
        'lifebuoy' => 'Support',
        'chat-bubble-left-right' => 'Chat',
        'user-group' => 'Team',
        'users' => 'Users',
        'academic-cap' => 'Education',
        'building-office-2' => 'Business',
        'briefcase' => 'Briefcase',
        'clipboard-document-check' => 'Checklist',
        'document-text' => 'Document',
        'envelope' => 'Email',
        'photo' => 'Photo',
        'film' => 'Video',
        'heart' => 'Heart',
        'star' => 'Star',
        'trophy' => 'Trophy',
        'hand-thumb-up' => 'Thumbs up',
        'check-badge' => 'Verified',
        'arrow-trending-up' => 'Growth',
        'currency-bangladeshi' => 'Taka',
        'truck' => 'Delivery',
        'qr-code' => 'QR code',
        'squares-2x2' => 'Grid / Apps',
        'window' => 'Web window',
        'link' => 'Link / Domain',
    ];

    /** Options for a Filament Select: ['heroicon-o-code-bracket' => 'Code', ...] */
    public static function options(): array
    {
        $options = [];

        foreach (self::LIST as $name => $label) {
            $options['heroicon-o-'.$name] = $label;
        }

        return $options;
    }
}
