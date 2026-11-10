<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use Illuminate\Database\Eloquent\Model;

class HostingPlan extends Model
{
    use HasActiveScope;

    public const TYPES = [
        'shared' => 'Shared Hosting',
        'wordpress' => 'WordPress Hosting',
        'reseller' => 'Reseller Hosting',
        'vps' => 'VPS Server',
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
