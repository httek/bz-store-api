<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $guarded = [];

    protected $casts = ['tags' => 'array', 'covers' => 'array', 'detail' => 'array'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id')
            ->select(['id', 'name', 'cover', 'level']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'id', 'delivery_id')
            ->select(['id', 'name', 'cost', 'type']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function review()
    {
        return $this->hasMany(UserReview::class, 'goods_id' , 'id')
            ->latest('star')
            ->latest('id')
            ->with('user');
    }
}
