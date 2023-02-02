<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    /**
     * @var string[]
     */
    protected $appends = ['items'];

    /**
     * @var string[]
     */
    protected $casts = ['meta' => 'array'];

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getItemsAttribute()
    {
        return Goods::with([])
            ->whereIn('id', $this->getAttributeValue('meta') ?: [])
            ->where('status', 1)
            ->get();
    }
}
