<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->getTable().'.is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->getTable().'.sort_order')->orderBy($this->getTable().'.id');
    }
}
