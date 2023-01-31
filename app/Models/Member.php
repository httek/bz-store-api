<?php

namespace App\Models;

use App\Service\TokenService;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $guarded = ['id'];

    /**
     * @param int $ttl
     * @return $this
     */
    public function withToken(int $ttl = 0)
    {
        $this->setAttribute('token', TokenService::issue($this, $ttl));

        return $this;
    }
}
