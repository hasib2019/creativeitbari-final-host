<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use Illuminate\Database\Eloquent\Model;

class DomainPrice extends Model
{
    use HasActiveScope;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'register_price' => 'decimal:2',
            'renew_price' => 'decimal:2',
            'transfer_price' => 'decimal:2',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /** Always store TLDs with a leading dot, lower-case: ".com" */
    public function setTldAttribute(string $value): void
    {
        $this->attributes['tld'] = '.'.ltrim(strtolower(trim($value)), '.');
    }
}
