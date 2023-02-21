<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReview extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['images' => 'json'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
