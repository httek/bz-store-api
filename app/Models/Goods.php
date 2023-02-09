<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $guarded = [];

    protected $casts = ['tags' => 'array', 'covers' => 'array'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id')
            ->select(['id', 'name', 'cover', 'level']);
    }
}
