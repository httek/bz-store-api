<?php

namespace App\Models;

use App\Services\TokenService;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['fake_id'];

    public function getFakeIdAttribute()
    {
        $year = Carbon::now()->format('y');
        return $id = str_pad($this->getKey() ?: 0, 3, '0', STR_PAD_LEFT);
    }

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
