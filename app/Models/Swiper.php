<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Swiper extends Model
{
    protected $casts = ['items' => 'json'];

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeAvaliable(Builder $query): Builder
    {
        return $query->where('status', 1)
            ->whereRaw(DB::raw("(NOW() between visible_begin and visible_ending)"));
    }
}
