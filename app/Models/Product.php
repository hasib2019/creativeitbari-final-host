<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasActiveScope;

    public const TYPES = [
        'saas' => 'SaaS (Subscription)',
        'one_time' => 'One-time Software',
        'ecommerce' => 'E-commerce Solution',
        'web_app' => 'Web Application',
    ];

    /** Icon + marketing copy per type, used in menus and listing filters. */
    public const TYPE_META = [
        'saas' => ['icon' => 'heroicon-o-cloud', 'label' => 'SaaS Products', 'description' => 'Cloud software on a monthly or yearly plan'],
        'one_time' => ['icon' => 'heroicon-o-key', 'label' => 'One-time Software', 'description' => 'Pay once, own the license forever'],
        'ecommerce' => ['icon' => 'heroicon-o-shopping-cart', 'label' => 'E-commerce', 'description' => 'Online stores ready to sell from day one'],
        'web_app' => ['icon' => 'heroicon-o-window', 'label' => 'Web Applications', 'description' => 'Custom business apps built for your workflow'],
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'features' => 'array',
            'plans' => 'array',
            'technologies' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /** The price a customer actually pays (sale price if set). */
    public function effectivePrice(): ?float
    {
        $price = $this->sale_price ?: $this->price;

        return $price !== null ? (float) $price : null;
    }

    public function hasDiscount(): bool
    {
        return $this->sale_price && $this->price && (float) $this->sale_price < (float) $this->price;
    }

    public function url(): string
    {
        return route('products.show', $this);
    }
}
